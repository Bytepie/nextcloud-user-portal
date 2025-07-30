<template>
	<NcAppSidebarTab id="user-details" name="User Details">
		<div class="user-details-container">
			<!-- Avatar Header -->
			<div class="user-header">
				<NcAvatar :url="user.avatar" :size="120" />
				<div class="user-header__text">
					<h2 class="user-header__name">
						{{ user.displayName }}
					</h2>
					<div class="user-header__id">
						{{ user.uid }}
					</div>
				</div>
			</div>

			<!-- Basic Info Section -->
			<div class="section-title">
				Basic Information
			</div>
			<div class="detail-grid">
				<NcTextField :model-value="user.email || 'Not set'" label="Email" readonly />
				<NcTextField :model-value="user.systemEmail || 'Not set'" label="System Email" readonly />
				<NcTextField :model-value="user.primaryEmail || 'Not set'" label="Primary Email" readonly />
				<NcTextField :model-value="formatLastLogin(user.lastLogin)" label="Last Login" readonly />
				<NcTextField v-model="user.cloudId" label="Cloud ID" readonly />
				<!-- <NcTextField :model-value="user.passwordHash" label="Password Hash" readonly /> -->
				<NcTextField :model-value="user.home" label="Home Directory" readonly />
				<NcTextField :model-value="user.backend" label="Storage Backend" readonly />
				<NcTextField :model-value="user.backendInstance" label="Backend Class" readonly />
			</div>

			<!-- Account Status -->
			<div class="section-title">
				Account Capabilities
			</div>
			<div class="detail-grid">
				<NcTextField :model-value="user.isAdmin ? 'Yes' : 'No'" label="Administrator" readonly />
				<NcTextField :model-value="user.enabled ? 'Yes' : 'No'" label="Account Enabled" readonly />
				<NcTextField :model-value="user.canChangePassword ? 'Yes' : 'No'" label="Can Change Password" readonly />
				<NcTextField :model-value="user.canChangeDisplayName ? 'Yes' : 'No'" label="Can Change Display Name" readonly />
				<NcTextField :model-value="user.canChangeAvatar ? 'Yes' : 'No'" label="Can Change Avatar" readonly />
			</div>

			<!-- Storage Info -->
			<div class="section-title">
				Storage Information
			</div>
			<div class="detail-grid">
				<NcTextField :model-value="formatBytes(user.storageInfo.used)" label="Used Space" readonly />
				<NcTextField :model-value="user.storageInfo.total || 'Unlimited'" label="Total Quota" readonly />
			</div>

			<!-- Groups -->
			<NcNoteCard v-if="user.groups.length" type="tip" class="section">
				<div class="section-title">
					Group Memberships
				</div>
				<div class="groups-container">
					<NcChip v-for="group in user.groups" :key="group" no-close>
						{{ group }}
					</NcChip>
				</div>
			</NcNoteCard>

			<!-- Managers -->
			<NcNoteCard v-if="user.managers?.length" type="tip" class="section">
				<div class="section-title">
					Managers
				</div>
				<div class="groups-container">
					<NcChip v-for="manager in user.managers" :key="manager">
						{{ manager }}
					</NcChip>
				</div>
			</NcNoteCard>
		</div>
	</NcAppSidebarTab>
</template>

<script setup>
import {
	NcAppSidebarTab,
	NcAvatar,
	NcTextField,
	NcNoteCard,
	NcChip,
} from '@nextcloud/vue'
import { ref, watch, toRaw, computed } from 'vue'

const props = defineProps({
	user: {
		type: Object,
		required: true,
	},
})

const user = ref({ ...toRaw(props.user) })

watch(
	() => props.user,
	(newVal) => {
		user.value = { ...toRaw(newVal) }
	},
	{ deep: true, immediate: true },
)

const formattedUserInfo = computed(() => ({
	Email: props.user.email,
	'System Email': props.user.systemEmail,
	'Primary Email': props.user.primaryEmail,
	'Last Login': formatLastLogin(props.user.lastLogin),
	'Cloud ID': props.user.cloudId,
	'Password Hash': props.user.passwordHash,
	'Home Directory': props.user.home,
	'Storage Backend': props.user.backend,
	'Backend Class': props.user.backendInstance,
}))

const formatBytes = (bytes, decimals = 2) => {
	if (bytes === 0) return '0 Bytes'
	const k = 1024
	const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
	const i = Math.floor(Math.log(bytes) / Math.log(k))
	return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i]
}

const formatLastLogin = (timestamp) => {
	if (!timestamp || timestamp === 0) return 'Never logged in'
	return new Date(timestamp * 1000).toLocaleString()
}
</script>

<style scoped lang="scss">

#MainContent {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;

}

.user-details-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 1rem;
}

.user-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;

  &__text {
    display: flex;
    flex-direction: column;
  }

  &__name {
    margin: 0;
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--color-main-text);
  }

  &__id {
    color: var(--color-text-lighter);
    font-size: 0.9rem;
  }
}

.section {
  margin-bottom: 0;

  .section-title {
    font-weight: bold;
    margin-bottom: 1rem;
    color: var(--color-main-text);
    font-size: 1.1rem;
  }
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.groups-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

:deep(.input-field__input:read-only) {
  background-color: var(--color-background-darker);
  border-color: var(--color-border);
  color: var(--color-text-light);
  cursor: default;
}

.section-title {
	font-size: 1.25rem;
	font-weight: bold;
	margin-top: 2rem;
	margin-bottom: 1rem;
	padding-bottom: 0.5rem;
	border-bottom: 2px solid #0075d4;
	color: #1a1a1a;
}

</style>
