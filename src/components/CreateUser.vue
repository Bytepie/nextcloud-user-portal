<template>
	<div id="MainContent">
		<h2>User Creation</h2>
		<form class="space-y-4" @submit.prevent="submitForm">
			<!-- Username Field -->
			<NcTextField
				v-model="form.username"
				required
				label="Username"
				trailing-button-icon="close"
				:show-trailing-button="form.username !== ''"
				@trailing-button-click="form.username = ''">
				<template #icon>
					<Magnify :size="20" />
				</template>
			</NcTextField>

			<!-- Display Name Field -->
			<NcTextField
				v-model="form.displayName"
				label="Display Name"
				trailing-button-icon="close"
				:show-trailing-button="form.displayName !== ''"
				@trailing-button-click="form.displayName = ''" />

			<!-- Password Field -->
			<NcTextField
				v-model="form.password"
				label="Password"
				type="password"
				required
				trailing-button-icon="close"
				:show-trailing-button="form.password !== ''"
				@trailing-button-click="form.password = ''">
				<template #icon>
					<Lock :size="20" />
				</template>
			</NcTextField>

			<!-- Quota Selection -->
			<div>
				<!-- Custom Quota Checkbox -->
				<div style="margin-top: 0.5rem">
					<NcCheckboxRadioSwitch v-model="useCustomQuota">
						Use Custom Qouta
					</NcCheckboxRadioSwitch>

					<NcSelect
						v-if="!useCustomQuota"
						v-model="selectedQuota"
						style="border: none"
						class="container__select"
						input-label="Select a Qouta"
						:options="quotaOptions"
						required />

					<!-- Custom Quota Field -->
					<NcTextField
						v-if="useCustomQuota"
						v-model="customQuota"
						label="Custom Quota"
						placeholder="e.g. 250 MB"
						trailing-button-icon="close"
						:show-trailing-button="customQuota !== ''"
						@trailing-button-click="customQuota = ''" />
				</div>

				<!-- Is Admin -->
				<div>
					<NcCheckboxRadioSwitch
						v-model="form.isAdmin"
						label="Toggle Admin"
						variant="warning"
						type="checkbox">
						Make Admin
					</NcCheckboxRadioSwitch>
					<br>
				</div>

				<!-- Submit Button -->
				<NcButton type="submit" variant="primary">
					<template #icon>
						<Plus :size="20" />
					</template>
					Create User
				</NcButton>
			</div>
		</form>
	</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
	NcTextField,
	NcButton,
	NcSelect,
	NcCheckboxRadioSwitch,
} from '@nextcloud/vue'
import Magnify from 'vue-material-design-icons/Magnify'
import Lock from 'vue-material-design-icons/Lock'
import Plus from 'vue-material-design-icons/Plus'

const form = ref({
	username: '',
	displayName: '',
	password: '',
	isAdmin: false,
})

const quotaOptions = ref([])
const selectedQuota = ref('')
const customQuota = ref('')
const useCustomQuota = ref(false)

onMounted(async () => {
	try {
		const res = await fetch('/apps/userportal/users/quotas')
		const data = await res.json()
		quotaOptions.value = data.options || []
	} catch (err) {
		console.error('Failed to fetch quota options:', err)
	}
})

const submitForm = async () => {
	const quota = useCustomQuota.value ? customQuota.value : selectedQuota.value

	const payload = {
		username: form.value.username,
		displayName: form.value.displayName,
		password: form.value.password,
		quota,
		isAdmin: form.value.isAdmin,
	}
	// console.log('I am running')
	try {
		const res = await fetch('/apps/userportal/users/create', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify(payload),
		})

		const result = await res.json()
		if (result.success) {
			alert('User created successfully.')
		} else {
			alert('Error: ' + result.error)
		}
	} catch (err) {
		console.error('Error creating user:', err)
	}
}
</script>

<style scoped></style>
