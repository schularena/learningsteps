<template>
	<main>
		<div id="conteneur-chargement" :class="{'chargement-parcours': chargementParcours, 'chargement-blocs': chargementBlocs}" v-if="chargement || chargementBlocs || chargementParcours">
			<div id="chargement">
				<div class="spinner"><div /><div /><div /><div /><div /><div /><div /><div /><div /><div /><div /><div /></div>
			</div>
		</div>
		<div id="conteneur-message" class="conteneur-modale" v-if="message">
			<div id="message" class="modale">
				<div class="conteneur">
					<div class="contenu">
						<div class="message" v-html="message" />
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="message = ''">{{ $t('fermer') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<router-view />
	</main>
</template>

<script>
export default {
	name: 'App',
	data () {
		return {
			hote: '',
			chargement: true,
			chargementBlocs: false,
			chargementParcours: true,
			message: '',
			notification: '',
			langues: ['de', 'fr', 'it', 'en'],
			langue: 'de'
		}
	},
	watch: {
		notification: function (notification) {
			if (notification !== '') {
				const element = document.createElement('div')
				const id = 'notification_' + Date.now().toString(36) + Math.random().toString(36).substr(2)
				element.id = id
				element.textContent = notification
				element.classList.add('notification')
				document.querySelector('#app').appendChild(element)
				this.notification = ''
				setTimeout(function () {
					element.parentNode.removeChild(element)
				}, 2000)
			}
		}
	},
	created () {
		this.hote = window.location.href.split('#')[0]
	}
}
</script>

<style src="destyle.css/destyle.css"></style>
<style src="@/assets/css/style.css"></style>
