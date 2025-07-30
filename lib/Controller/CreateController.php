<?php

namespace OCA\UserPortal\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;
use OCP\IUserManager;
use OCP\IGroupManager;
use OCP\IUserSession;
use OCP\AppFramework\Http;
use OCP\ILogger;

class CreateController extends ApiController {
    private IUserManager $userManager;
    private IGroupManager $groupManager;
    private IUserSession $userSession;
    private ILogger $logger;

    public function __construct(
        string $appName,
        IRequest $request,
        IUserManager $userManager,
        IGroupManager $groupManager,
        IUserSession $userSession,
        ILogger $logger
    ) {
        parent::__construct($appName, $request);
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->userSession = $userSession;
        $this->logger = $logger;
    }

    /**
     * Return predefined quota options
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getQuotaOptions(): DataResponse {
        return new DataResponse([
            'options' => [
                '1 GB',
                '5 GB',
                '10 GB',
                '50 GB',
                'Unlimited'
            ]
        ]);
    }

    /**
     * Create a new user
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function createUser(string $username, string $displayName, string $password, string $quota, bool $isAdmin): DataResponse {

        $currentUser = $this->userSession->getUser();
        if (!$currentUser || !$this->groupManager->isAdmin($currentUser->getUID())) {
            return new DataResponse(['error' => 'Unauthorized'], Http::STATUS_UNAUTHORIZED);
        }

        if ($this->userManager->userExists($username)) {
            return new DataResponse(['error' => 'User already exists'], Http::STATUS_CONFLICT);
        }

        $user = $this->userManager->createUser($username, $password);
        if (!$user) {
            return new DataResponse(['error' => 'Failed to create user'], Http::STATUS_INTERNAL_SERVER_ERROR);
        }

        try {
            $user->setDisplayName($displayName);

            // Handle quota: set to 'none' if unlimited
            $user->setQuota($quota === 'Unlimited' ? 'none' : $quota);
        } catch (\Throwable $e) {
            $this->logger->warning("Failed to set user attributes: " . $e->getMessage(), ['app' => 'iamdiskbg']);
        }

        if ($isAdmin) {
            $adminGroup = $this->groupManager->get('admin');
            if ($adminGroup) {
                $adminGroup->addUser($user);
            }
        }

        return new DataResponse(['success' => true]);
    }
}
