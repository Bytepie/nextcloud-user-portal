<template>
	<div class="password-strength">
		<div class="strength-meter">
			<div
				class="strength-bar"
				:class="strengthClass"
				:style="{ width: strength + '%' }" />
		</div>
		<div class="strength-text">
			{{ strengthText }}
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
	password: {
		type: String,
		default: '',
	},
})

const strength = computed(() => {
	if (!props.password) return 0

	let score = 0
	// Length
	score += Math.min(props.password.length * 5, 30)
	// Variety
	if (/[A-Z]/.test(props.password)) score += 10
	if (/[0-9]/.test(props.password)) score += 10
	if (/[^A-Za-z0-9]/.test(props.password)) score += 15
	// Deductions for common patterns
	if (props.password.length < 6) score -= 10
	if (/^[a-z]+$/i.test(props.password)) score -= 5
	if (/^[0-9]+$/.test(props.password)) score -= 15

	return Math.min(Math.max(score, 0), 100)
})

const strengthClass = computed(() => {
	if (strength.value < 30) return 'weak'
	if (strength.value < 70) return 'medium'
	return 'strong'
})

const strengthText = computed(() => {
	if (!props.password) return ''
	if (strength.value < 30) return 'Weak'
	if (strength.value < 70) return 'Medium'
	return 'Strong'
})
</script>

<style scoped>
.password-strength {
  margin-top: 5px;
}

.strength-meter {
  height: 5px;
  background-color: var(--color-background-darker);
  border-radius: 3px;
  overflow: hidden;
}

.strength-bar {
  height: 100%;
  transition: width 0.3s ease;
}

.strength-bar.weak {
  background-color: var(--color-error);
}

.strength-bar.medium {
  background-color: var(--color-warning);
}

.strength-bar.strong {
  background-color: var(--color-success);
}

.strength-text {
  font-size: 0.8em;
  color: var(--color-text-lighter);
  text-align: right;
}
</style>
