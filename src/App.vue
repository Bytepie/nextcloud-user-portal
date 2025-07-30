<template>
	<NcContent app-name="userportal">
		<div id="main-navigation">
			<NcAppNavigation>
				<template #list>
					<NcAppNavigationItem
						name="Users List"
						title="User List"
						:class="{ active: currentPage === 'userslist' }"
						@click="currentPage = 'userslist'">
						<template #icon>
							<AccountMultiple :size="20" />
						</template>
					</NcAppNavigationItem>

					<NcAppNavigationItem
						name="Create User"
						title="Create User"
						:class="{ active: currentPage === 'createuser' }"
						@click="currentPage = 'createuser'">
						<template #icon>
							<AccountPlus :size="20" />
						</template>
					</NcAppNavigationItem>
				</template>
				<template #footer>
					<div class="navigation__footer">
						<NcButton
							wide
							:class="{ active: currentPage === 'settings' }"
							@click="currentPage = 'settings'">
							<template #icon>
								<IconCog />
							</template>
							App settings
						</NcButton>
					</div>
				</template>
			</NcAppNavigation>
		</div>
		<NcAppContent>
			<UserPage v-if="currentPage === 'userslist'" />
			<CreateUser v-if="currentPage === 'createuser'" />
		</NcAppContent>
		<NcAppSidebar v-if="showDetailsDialog" @close="closeSidebar">
			<UserDetailsSidebar :user="sidebarContent" />
		</NcAppSidebar>
	</NcContent>
</template>

<script setup>
import { ref, provide } from 'vue'
import {
	NcContent,
	NcAppNavigation,
	NcAppNavigationItem,
	NcAppContent,
	NcButton,
	NcAppSidebar,
} from '@nextcloud/vue'
import UserPage from './components/UserPage.vue'
import AccountMultiple from 'vue-material-design-icons/AccountMultiple'
import AccountPlus from 'vue-material-design-icons/AccountPlus'
import IconCog from 'vue-material-design-icons/Cog'
import CreateUser from './components/CreateUser.vue'
import UserDetailsSidebar from './components/UserDetailsSidebar.vue'
const currentPage = ref('userslist') // default view

// Block to catch data from child
const showDetailsDialog = ref(false)
const sidebarContent = ref('') // optionally dynamic perhaps may not need it

function openSidebar(content = '') {
	sidebarContent.value = content
	showDetailsDialog.value = true
}

function closeSidebar() {
	showDetailsDialog.value = false
}

provide('openSidebar', openSidebar)
</script>

<style scoped lang="scss">
#userportal {
  display: flex;
  justify-content: center;
//   margin-top: 40px;
}

// #MainContent {
//   display: flex;
//   flex-direction: column;
//   gap: 1.5rem;
//   width: 100%;
//   max-width: 1200px;
//   margin: 0 auto;

// }

.navigation__header,
.navigation__footer {
  margin: 1rem;
}

:deep(.active) {
  > .app-navigation-entry {
    background-color: #00b3f0;
  }
  .app-navigation-entry:hover {
    background-color: #0286b3;
  }

  > .app-navigation-entry a {
    font-weight: bold;
    // background-color: black;
  }
}

:deep(.navigation__footer) {
  > .button-vue--secondary {
    background-color: #00b3f0;
  }
  .button-vue--secondary:hover {
    background-color: #0286b3;
  }
}

#main-navigation {
  background-color: #035a85;
}

h2 {
  font-weight: 600;
  font-size: 16px;
  margin-left: auto;
  margin-right:auto;
  text-align: center;
}

</style>
