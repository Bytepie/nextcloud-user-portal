<?php

namespace OCA\UserPortal\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\IUser;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\IGroupManager;
use OCP\Files\IRootFolder;

class UserController extends Controller
{
    private IUserManager $userManager;
    private $urlGenerator;
    private IUserSession $userSession;
    private IGroupManager $groupManager;
    private IRootFolder $rootFolder;


    public function __construct(
        string $AppName,
        IRequest $request,
        IUserManager $userManager,
        IURLGenerator $urlGenerator,
        IUserSession $userSession,
        IGroupManager $groupManager,
        IRootFolder $rootFolder,
    ) {
        parent::__construct($AppName, $request);
        $this->userManager = $userManager;
        $this->urlGenerator = $urlGenerator;
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
        $this->rootFolder = $rootFolder;
    }

    /**
     * @NoAdminRequired
     * *@NoCSRFRequired
     * *@PublicPage
     */
    public function index(IRequest $request): DataResponse
    {
        // Nextcloud (via Symfony under the hood) automatically binds query parameters to controller method arguments if:
        // The HTTP method is GET.
        // The parameters are in the URL as ?limit=5&offset=10
        // The controller method has matching parameter names.
        // So when your frontend does:
        // GET /apps/userportal/users?limit=5&offset=10
        // Then Nextcloud sees:
        // public function index(int $limit = 5, int $offset = 0)
        // changed this code to get IRequest parameters directly without implicit mapping

        $limit = (int) $request->getParam('limit', 5);
        $offset = (int) $request->getParam('offset', 0);
        $search = trim((string) $request->getParam('search', ''));

        $users = $this->userManager->search($search, $limit, $offset);

        // Filtered count if you want accurate pagination for search
        $totalUsers = count($this->userManager->search($search));

        $result = array_map(function (IUser $user) {
            return [
                'uid' => $user->getUID(),
                'displayname' => $user->getDisplayName(),
                'email' => $user->getEMailAddress(),
                'enabled' => $user->isEnabled(),
                'qouta' => $user->getQuota(),
                'lastLogin' => $user->getLastLogin(),
                'avatar' => $this->urlGenerator->linkToRouteAbsolute(
                    'core.avatar.getAvatar',
                    ['userId' => $user->getUID(), 'size' => 64]
                )
            ];
        }, $users);

        return new DataResponse([
            'users' => $result,
            'total' => $totalUsers,
            'limit' => $limit,
            'offset' => $offset,
            'search' => $search,
        ]);
    }

    /**
     * Get detailed information about a specific user
     * 
     * @param string $userId The user ID to get details for
     * @return DataResponse
     * @NoCSRFRequired
     */
    public function getDetails(string $uid): DataResponse
    {
        $user = $this->userManager->get($uid);


        if ($user === null) {
            return new DataResponse(
                ['message' => 'User not found'],
                Http::STATUS_NOT_FOUND
            );
        }

        $details = [
            // Basic information
            'uid' => $user->getUID(),
            'displayName' => $user->getDisplayName(),
            'enabled' => $user->isEnabled(),

            // Email information
            'email' => $user->getEMailAddress(),
            'systemEmail' => $user->getSystemEMailAddress(),
            'primaryEmail' => $user->getPrimaryEMailAddress(),

            // Authentication information
            'lastLogin' => $user->getLastLogin(),
            'canChangePassword' => $user->canChangePassword(),
            // 'passwordHash' => $user->getPasswordHash(),

            // Storage information
            'quota' => $user->getQuota(),
            'home' => $user->getHome(),

            // Backend information
            'backend' => $user->getBackendClassName(),
            'backendInstance' => $user->getBackend() ? get_class($user->getBackend()) : null,

            // Profile capabilities
            'canChangeAvatar' => $user->canChangeAvatar(),
            'canChangeDisplayName' => $user->canChangeDisplayName(),

            // Federation
            'cloudId' => $user->getCloudId(),

            // Management
            'managers' => $user->getManagerUids(),

            // Avatar URLs
            'avatar' => $this->urlGenerator->linkToRouteAbsolute(
                'core.avatar.getAvatar',
                ['userId' => $user->getUID(), 'size' => 512]
            ),

            // Additional calculated information
            'isAdmin' => $this->groupManager->isInGroup($uid, 'admin'),
            'groups' => $this->groupManager->getUserGroupIds($user),
            'storageInfo' => $this->getUserStorageInfo($user)
        ];

        return new DataResponse($details);
    }

    /**
     * Helper method to get user storage information
     */
    private function getUserStorageInfo(IUser $user): array
    {
        try {
            $userId = $user->getUID();
            $userFolder = $this->rootFolder->getUserFolder($userId);
            $storage = $userFolder->getStorage();

            // Get the root path for this user's storage
            $rootPath = $userFolder->getPath();

            // Calculate storage metrics
            $used = $userFolder->getSize();
            $free = $storage->free_space($rootPath);  // Now passing the required path parameter
            // $total = $storage->total_space($rootPath); // Some implementations may require path too

            return [
                'used' => $used,
                // 'free' => $free !== false ? $free : 0,
                'total' => $user->getQuota() == 'none' ? 'Unlimited' : $user->getQuota()
                // 'relative' => $total > 0 ? ($used / $total) : 0,
                // 'quota' => $user->getQuota()
            ];
        } catch (\Exception $e) {
            return [
                'used' => 0,
                // 'free' => 0,
                'total' => 0,
                // 'relative' => 0,
                // 'quota' => $user->getQuota(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @NoAdminRequired
     */
    public function create(): DataResponse
    {
        $uid = $this->request->getParam('uid');
        $password = $this->request->getParam('password');

        if ($this->userManager->userExists($uid)) {
            return new DataResponse(['error' => 'User already exists'], 400);
        }

        $user = $this->userManager->createUser($uid, $password);

        return new DataResponse([
            'uid' => $user->getUID()
        ]);
    }

    /**
     * @NoAdminRequired
     */
    public function disable(string $uid): DataResponse
    {
        $user = $this->userManager->get($uid);
        if (!$user) return new DataResponse(['error' => 'Not found'], 404);

        if ($this->userSelfCheck($uid)) {
            return new DataResponse(['error' => 'You cannot disable yourself'], Http::STATUS_FORBIDDEN);
        }

        $user->setEnabled(false);
        return new DataResponse(['message' => 'User disabled']);
    }

    // Check if user is trying to disable themselves
    public function userSelfCheck(string $uid)
    {
        $user = $this->userManager->get($uid);
        $currentUser = $this->userSession->getUser();
        // Check if user is trying to disable themselves
        if ($currentUser->getUID() === $uid) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @NoAdminRequired
     */
    public function enable(string $uid): DataResponse
    {
        $user = $this->userManager->get($uid);
        if (!$user) return new DataResponse(['error' => 'Not found'], 404);
        $user->setEnabled(true);
        return new DataResponse(['message' => 'User enabled']);
    }

    /**
     * @NoAdminRequired
     */
    public function delete(string $uid): DataResponse
    {
        $user = $this->userManager->get($uid);
        if (!$user) return new DataResponse(['error' => 'Not found'], 404);

        if ($this->userSelfCheck($uid)) {
            return new DataResponse(['error' => 'You cannot delete yourself'], Http::STATUS_FORBIDDEN);
        }

        $user->delete();
        return new DataResponse(['message' => 'User deleted']);
    }
}
