<template>
	<div>
		<div id="MainContent">
			<NcDialog
				v-if="showDisableDialog"
				:name="t('userportal', 'Disable user')"
				:message="
					t('userportal', 'Are you sure you want to disable {user}?', {
						user: selectedUser,
					})
				"
				:buttons="disableButtons" />

			<NcDialog
				v-if="showDeleteDialog"
				:name="t('userportal', 'Delete user')"
				:message="
					t('userportal', 'Are you sure you want to permanently delete {user}?', {
						user: selectedUser,
					})
				"
				:buttons="deleteButtons" />
			<NcDialog
				v-if="showDetailsDialog"
				:name="t('userportal', 'User Details')"
				:message="
					t('userportal', 'Are you sure you want to permanently delete {user}?', {
						user: selectedUser,
					})
				"
				:buttons="deleteButtons" />
			<h2>User Manager</h2>

			<!-- Search and Filter Controls -->
			<div class="controls">
				<NcTextField
					v-model="searchQuery"
					placeholder="Search users..."
					:show-trailing-button="searchQuery !== ''"
					trailing-button-icon="close"
					@update:model-value="handleSearch"
					@trailing-button-click="clearSearch" />

				<div class="pagination-controls">
					<NcButton
						variant="primary"
						type="button"
						:disabled="offset === 0"
						@click="previousPage">
						Previous
					</NcButton>

					<span class="page-info">
						Page {{ currentPage }} of {{ totalPages }}
					</span>

					<NcButton
						variant="primary"
						type="button"
						:disabled="offset + limit >= totalUsers"
						@click="nextPage">
						Next
					</NcButton>
				</div>
				<span>Total users: {{ totalUsers }}</span>
			</div>

			<!-- User Table -->
			<div class="table-container">
				<table class="table">
					<thead>
						<tr>
							<th>User ID</th>
							<th>Avatar</th>
							<th>Display Name</th>
							<th>Email</th>
							<th>Qouta</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="user in users" :key="user.uid">
							<td>{{ user.uid }}</td>
							<td>
								<NcAvatar :url="user.avatar" />
							</td>
							<td>{{ user.displayname }}</td>
							<td>{{ user.email }}</td>
							<td>{{ user.qouta === "none" ? "Unlimited" : user.qouta }}</td>
							<td>
								<span
									v-if="user.enabled"
									class="user-status user-status--enabled">
									<span class="user-status-icon" />
									Enabled
								</span>
								<span v-else class="user-status user-status--disabled">
									<span class="user-status-icon" />
									Disabled
								</span>
							</td>
							<td class="actions">
								<NcButton
									variant="primary"
									type="tertiary"
									@click="handleSidebar(user.uid)">
									<template #icon>
										<IconCog :size="20" />
									</template>
								</NcButton>
								<NcButton
									v-if="user.enabled"
									type="tertiary"
									variant="warning"
									@click="confirmDisableUser(user.uid)">
									<template #icon>
										<Stop :size="20" />
									</template>
								</NcButton>
								<NcButton
									v-else
									variant="success"
									type="tertiary"
									@click="enableUser(user.uid)">
									<template #icon>
										<Play :size="20" />
									</template>
								</NcButton>
								<NcButton
									variant="error"
									type="tertiary-error"
									@click="confirmDeleteUser(user.uid)">
									<template #icon>
										<Delete :size="20" />
									</template>
								</NcButton>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Loading Indicator -->
			<NcEmptyContent v-if="loading" :title="t('userportal', 'Loading users...')">
				<template #icon>
					<NcLoadingIcon />
				</template>
			</NcEmptyContent>

			<!-- No Results Message -->
			<NcEmptyContent
				v-else-if="users.length === 0"
				:title="t('userportal', 'No users found')" />
		</div>
	</div>
</template>

<script setup>
// Confirmation dialog component
import { ref, computed, onMounted, inject } from 'vue'
import {
	NcButton,
	NcTextField,
	NcEmptyContent,
	NcLoadingIcon,
	NcDialog,
	showError,
	NcAvatar,
} from '@nextcloud/vue'
import { t } from '@nextcloud/l10n'
import IconCog from 'vue-material-design-icons/AccountDetails'
import Stop from 'vue-material-design-icons/Stop'
import Play from 'vue-material-design-icons/Play'
import Delete from 'vue-material-design-icons/Delete'

// const t = useTranslator()

// State
const users = ref([])
const loading = ref(false)
const searchQuery = ref('')
const limit = ref(10)
const offset = ref(0)
const totalUsers = ref(0)

// Dialog states
const showDisableDialog = ref(false)
const showDeleteDialog = ref(false)
const selectedUser = ref(null)
const showDetailsDialog = ref(false)
const openSidebar = inject('openSidebar')

async function handleSidebar(id) {
	try {
		// console.log('Fetching details for user:', id)
		const userDetails = await getUserDetails(id)
		// console.log('Received user details:', userDetails)
		openSidebar?.(userDetails) // Pass the resolved data
	} catch (error) {
		console.error('Failed to load user details:', error)
		// Optionally show error to user
		showError(t('userportal', 'Failed to load user details'))
	}
}

const disableButtons = ref([
	{
		label: t('userportal', 'Cancel'),
		variant: 'primary',
		callback: () => {
			showDisableDialog.value = false
		},
	},
	{
		label: t('userportal', 'Disable'),
		variant: 'error',
		callback: () => {
			disableUserConfirm(selectedUser.value)
			showDisableDialog.value = false
		},
	},
])

const deleteButtons = ref([
	{
		label: t('userportal', 'Cancel'),
		variant: 'primary',
		callback: () => {
			showDeleteDialog.value = false
		},
	},
	{
		label: t('userportal', 'Delete'),
		variant: 'error',
		callback: () => {
			deleteUserConfirm(selectedUser.value)
			showDeleteDialog.value = false
		},
	},
])

// User actions with dialogs
const confirmDisableUser = (userId) => {
	selectedUser.value = userId
	showDisableDialog.value = true
}

const confirmDeleteUser = (userId) => {
	selectedUser.value = userId
	showDeleteDialog.value = true
}

const disableUserConfirm = async (id) => {
	try {
		const response = await fetch(
			OC.generateUrl(`/apps/userportal/users/${id}/disable`),
			{
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					requesttoken: OC.requestToken,
				},
			},
		)
		const data = await response.json().catch(() => ({}))
		// const rawText = await response.text()
		// console.log('Raw Response:', rawText)
		if (!response.ok) {
			// For example: 403 from "You cannot disable yourself"
			// console.log(data.error)
			const errorMessage
        = data.error || t('userportal', 'Failed to disable user')
			OC.Notification.show(errorMessage)
			throw new Error(errorMessage)
		}
		const successMessage
      = data.message || t('userportal', 'User disabled successfully')
		OC.Notification.showTemporary(successMessage)
		fetchUsers()
	} catch (error) {
		// OC.Notification.showTemporary(error.message)
		console.error('Disable user error:', error)
	}
}

const deleteUserConfirm = async (id) => {
	try {
		const response = await fetch(
			OC.generateUrl(`/apps/userportal/users/${id}`),
			{
				method: 'DELETE',
				headers: {
					'Content-Type': 'application/json',
					requesttoken: OC.requestToken,
				},
			},
		)
		const data = await response.json().catch(() => ({}))
		// const rawText = await response.text()
		// console.log('Raw Response:', rawText)
		if (!response.ok) {
			// For example: 403 from "You cannot disable yourself"
			// console.log(data.error)
			const errorMessage
        = data.error || t('userportal', 'Failed to Delete user')
			OC.Notification.show(errorMessage)
			throw new Error(errorMessage)
		}
		const successMessage
      = data.message || t('userportal', 'User deleted successfully')
		OC.Notification.showTemporary(successMessage)
		fetchUsers()
	} catch (error) {
		// OC.Notification.showTemporary(error.message)
		console.error('Disable user error:', error)
	}
}

// Fetch users with pagination
const fetchUsers = async () => {
	try {
		loading.value = true
		const url
      = OC.generateUrl('/apps/userportal/users')
      + `?limit=${limit.value}&offset=${offset.value}`
      + (searchQuery.value
      	? `&search=${encodeURIComponent(searchQuery.value)}`
      	: '')

		const res = await fetch(url, {
			headers: {
				requesttoken: OC.requestToken,
			},
		})

		if (!res.ok) throw new Error(res.statusText)

		const data = await res.json()
		// Console.log(data.users)
		users.value = data.users
		totalUsers.value = data.total
	} catch (error) {
		showError(t('userportal', 'Failed to load users'))
		console.error(error)
	} finally {
		loading.value = false
	}
}

// Get Detail of user
const getUserDetails = async (id) => {
	try {
		loading.value = true
		const url = OC.generateUrl(`/apps/userportal/users/${id}/details`)

		const response = await fetch(url, {
			headers: {
				requesttoken: OC.requestToken,
				Accept: 'application/json',
			},
		})

		if (!response.ok) {
			throw new Error(`HTTP error! status: ${response.status}`)
		}

		const data = await response.json()
		return data
	} catch (error) {
		showError(t('userportal', 'Failed to load user details: {error}', { error: error.message }))
		console.error('Error fetching user details:', error)
		throw error
	} finally {
		loading.value = false
	}
}

// Computed properties
const currentPage = computed(() => Math.floor(offset.value / limit.value) + 1)
const totalPages = computed(() => Math.ceil(totalUsers.value / limit.value))

// Pagination controls
const nextPage = () => {
	offset.value += limit.value
	fetchUsers()
}

const previousPage = () => {
	offset.value = Math.max(0, offset.value - limit.value)
	fetchUsers()
}

// Search handling
const handleSearch = () => {
	offset.value = 0
	fetchUsers()
}

const clearSearch = () => {
	searchQuery.value = ''
	handleSearch()
}

const enableUser = async (id) => {
	try {
		await fetch(OC.generateUrl(`/apps/userportal/users/${id}/enable`), {
			method: 'POST',
			headers: {
				requesttoken: OC.requestToken,
			},
		})
		OC.Notification.showTemporary(t('userportal', 'User enabled successfully'))
		fetchUsers()
	} catch (error) {
		showError(t('userportal', 'Failed to enable user'))
	}
}

// Initial loads
onMounted(fetchUsers)

</script>

<style scoped lang="scss">
h2 {
  font-weight: 600;
  font-size: 16px;
}
#MainContent {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.controls {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;

  .pagination-controls {
    display: flex;
    align-items: center;
    gap: 1rem;

    .page-info {
      font-weight: bold;
      min-width: 100px;
      text-align: center;
    }
  }
}

.table-container {
  width: 100%;
  overflow-x: auto;
  background-color: var(--color-main-background);
  border-radius: var(--border-radius-large);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table {
  width: 100%;
  border-collapse: collapse;

  th {
    text-align: left;
    padding: 12px 16px;
    background-color: var(--color-background-dark);
    border-bottom: 1px solid var(--color-border);
    font-weight: bold;
  }

  td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--color-border);
    vertical-align: middle;
  }
}

.user-status {
  display: inline-flex;
  align-items: center;
  gap: 8px;

  &-icon {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
  }

  &--enabled &-icon {
    background-color: var(--color-success);
  }

  &--disabled &-icon {
    background-color: var(--color-error);
  }
}

.actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

@media (max-width: 768px) {
  .controls {
    flex-direction: column;
    align-items: stretch;

    .pagination-controls {
      justify-content: center;
    }
  }

  .table th,
  .table td {
    padding: 8px 12px;
  }

  .actions {
    flex-direction: column;
    gap: 4px;
  }
}
</style>
