<template>
	<div id="page">
		<div id="accueil">
			<div id="langues">
				<span class="bouton" role="button" tabindex="0" :class="{'selectionne': $parent.$parent.langue === 'de'}" @click="modifierLangue('de')">DE</span>
				<span class="bouton" role="button" tabindex="0" :class="{'selectionne': $parent.$parent.langue === 'fr'}" @click="modifierLangue('fr')">FR</span>
				<span class="bouton" role="button" tabindex="0" :class="{'selectionne': $parent.$parent.langue === 'it'}" @click="modifierLangue('it')">IT</span>
				<span class="bouton" role="button" tabindex="0" :class="{'selectionne': $parent.$parent.langue === 'en'}" @click="modifierLangue('en')">EN</span>
			</div>
			<div id="conteneur">
				<div id="contenu">
					<h1>
						<span>Learningsteps</span>
					</h1>
					<div>
						<p v-html="$t('slogan')" />
						<span id="bouton" role="button" tabindex="0" @click="ouvrirModaleParcours">{{ $t('creerParcours') }}</span>
					</div>
				</div>
				<div id="credits">
                    <p><a href="https://www.paypal.me/schularenacom" target="_blank" rel="noreferrer">{{ $t('soutien') }}</a></p>
                    <p>Learningsteps – ein Webtool von ABALIR AG <span style="font-size: 90%; opacity: .8">(<a href="https://github.com/schularena/learningsteps" target="_blank" rel="noreferrer">{{ $t('codeSource') }}</a>)</span></p>
                    <p>© {{ new Date().getFullYear() }} ABALIR AG</p>
				</div>
			</div>
		</div>
		<div class="conteneur-modale" v-if="modale === 'parcours'">
			<div class="modale">
				<header>
					<span class="titre">{{ $t('nouveauParcours') }}</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleParcours"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<label>{{ $t('nomParcours') }}</label>
						<input type="text" :value="nom" @input="nom = $event.target.value">
						<label>{{ $t('questionSecrete') }}</label>
						<select :value="question" @change="question = $event.target.value">
							<option value="" selected>-</option>
							<option v-for="(item, index) in questions" :value="item" :key="'option_' + index">{{ $t(item) }}</option>
						</select>
						<label>{{ $t('reponseSecreteEdition') }}</label>
						<input type="text" :value="reponse" @input="reponse = $event.target.value" @keydown.enter="creerParcours">
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="creerParcours">{{ $t('creer') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Accueil-Digisteps',
	data () {
		return {
			modale: '',
			nom: '',
			question: '',
			questions: ['motPrefere', 'filmPrefere', 'chansonPreferee', 'prenomMere', 'prenomPere', 'nomRue', 'nomEmployeur', 'nomAnimal'],
			reponse: '',
			hub: false
		}
	},
	created () {
		this.$parent.$parent.chargementParcours = false
		const langue = this.$route.query.lang
		if (this.$parent.$parent.langues.includes(langue) === true) {
			this.$parent.$parent.langue = langue
			localStorage.setItem('digisteps_lang', langue)
		}
	},
	mounted () {
		const langue = navigator.language.substring(0, 2)
		if (this.$parent.$parent.langues.includes(this.$route.query.lang) === false && this.$parent.$parent.langues.includes(langue) === true) {
			this.$parent.$parent.langue = langue
		}
		if (localStorage.getItem('digisteps_lang')) {
			this.$parent.$parent.langue = localStorage.getItem('digisteps_lang')
		}
		this.$root.$i18n.locale = this.$parent.$parent.langue
		document.getElementsByTagName('html')[0].setAttribute('lang', this.$parent.$parent.langue)
		setTimeout(function () {
			this.$parent.$parent.chargement = false
		}.bind(this), 300)
	},
	methods: {
		modifierLangue (langue) {
			this.$root.$i18n.locale = langue
			this.$parent.$parent.langue = langue
			document.getElementsByTagName('html')[0].setAttribute('lang', langue)
			this.$parent.$parent.notification = this.$t('langueModifiee')
			localStorage.setItem('digisteps_lang', langue)
		},
		ouvrirModaleParcours () {
			this.modale = 'parcours'
		},
		fermerModaleParcours () {
			this.modale = ''
			this.nom = ''
			this.question = ''
			this.reponse = ''
		},
		creerParcours () {
			if (this.nom !== '' && this.question !== '' && this.reponse !== '') {
				this.$parent.$parent.chargement = true
				const xhr = new XMLHttpRequest()
				xhr.onload = function () {
					if (xhr.readyState === xhr.DONE && xhr.status === 200) {
						this.$parent.$parent.chargement = false
						this.fermerModaleParcours()
						if (xhr.responseText !== 'erreur') {
							this.$router.push('/s/' + xhr.responseText)
						} else {
							this.$parent.$parent.message = this.$t('erreurServeur')
						}
					} else {
						this.$parent.$parent.chargement = false
						this.fermerModaleParcours()
						this.$parent.$parent.message = this.$t('erreurServeur')
					}
				}.bind(this)
				xhr.open('POST', this.$parent.$parent.hote + 'inc/creer_parcours.php', true)
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
				xhr.send('nom=' + this.nom + '&question=' + this.question + '&reponse=' + this.reponse)
			} else if (this.nom === '') {
				this.$parent.$parent.message = this.$t('remplirTitre')
			} else if (this.question === '') {
				this.$parent.$parent.message = this.$t('selectionnerQuestionSecrete')
			} else if (this.reponse === '') {
				this.$parent.$parent.message = this.$t('remplirReponseSecrete')
			}
		},
		ouvrirHub () {
			this.hub = true
		},
		fermerHub () {
			this.hub = false
		}
	}
}
</script>

<style scoped>
#page,
#accueil {
	width: 100%;
	height: 100vh;
}

#accueil {
    color: white;
    background-image: url('../../public/static/img/bg.jpg');
	background-size: cover;
	background-position: center;
	background-repeat: no-repeat;
}

#langues {
	position: fixed;
	display: flex;
	top: 10px;
	right: 5px;
	z-index: 10;
}

#langues span {
    display: flex;
    justify-content: center;
	align-items: center;
	font-size: 14px;
    width: 30px;
	height: 30px;
	background: #ffffff5e;
    border-radius: 50%;
    border: 1px solid #ddd;
    margin-right: 10px;
	cursor: pointer;
}

#langues span.selectionne {
    background: #242f3d;
    color: #fff;
    border: 1px solid #222;
    cursor: default;
}

#conteneur {
	position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
	flex-wrap: wrap;
	overflow: auto;
}

#contenu {
	max-width: 76em;
	text-align: center;
	padding: 12em 1em 6em;
	margin: auto;
}

#conteneur h1 {
    text-shadow: 1px 1px 1px rgb(0 0 0 / 20%);
    font-size: 3em;
	font-weight: 900;
    margin-bottom: 0.85em;
    line-height: 1.4;
}

#conteneur p {
    font-size: 1.25em;
    font-weight: 400;
    line-height: 1.4;
    margin-bottom: 1.5em;
}

#credits {
	width: 100%;
	margin: 0 auto 0.75em;
}

#credits p {
    font-size: 1em;
    font-weight: 400;
    line-height: 1.2;
    margin-bottom: 1em;
	text-align: center;
}

#credits p:last-child {
	display: flex;
	justify-content: center;
	align-items: center;
}

#credits p:last-child a {
	margin: 0 5px;
}

#credits .hub {
	font-size: 0;
	cursor: pointer;
}

#hub {
	position: fixed;
	visibility: hidden;
	opacity: 0;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
	z-index: -1;
}

#hub.ouvert {
	visibility: visible;
	opacity: 1;
    animation: fonduEntrant linear 0.1s;
	z-index: 100000;
}

@keyframes fonduEntrant {
    from { opacity: 0; }
    to   { opacity: 1; }
}

#hub iframe {
	width: 100%;
    height: 100%;
}

#hub span {
	font-size: 0;
	color: #fff;
	position: absolute;
	top: 15px;
	right: 15px;
	cursor: pointer;
}

@media screen and (max-width: 359px) {
	#contenu {
		padding: 4em 1em 2em;
	}

	#bouton {
		font-size: 0.75em!important;
	}
}

@media screen and (min-width: 360px) and (max-width: 599px) {
	#contenu {
		padding: 5em 1em 2.5em;
	}
}

@media screen and (max-width: 399px) {
	#conteneur h1 span {
		display: block;
	}
}

@media screen and (max-width: 599px) {
	#conteneur h1 {
		font-size: 2em;
		margin-bottom: 1em;
	}

	#conteneur p {
		font-size: 1em;
		margin-bottom: 1.2em;
	}
	
	#bouton {
		font-size: 0.85em;
	}
	
	#credits p {
		font-size: 0.85em;
	}

	#hub span {
		top: 5px;
		right: 5px;
	}
	
	#hub span svg {
		width: 24px;
		height: 24px;
	}
}

@media screen and (max-width: 599px) and (orientation: landscape) {
	#contenu {
		padding: 2em 1em 1.5em!important;
	}
}

@media screen and (min-width: 600px) and (max-width: 820px) and (orientation: landscape) {
	#contenu {
		padding: 3em 1em 1.5em!important;
	}
}

@media screen and (max-width: 820px) and (orientation: landscape) {
	#conteneur p {
		font-size: 1em!important;
	}
	
	#credits p {
		font-size: 0.85em!important;
		margin-bottom: 0.85em!important;
	}
}

@media screen and (max-width: 1023px) and (orientation: landscape) {
	#contenu {
		padding: 7em 1em 3.5em;
	}
}

@media screen and (max-width: 767px) {
	#langues {
		top: 9px;
		right: 4px;
	}

	#langues span {
		font-size: 12px;
		width: 27px;
		height: 27px;
		margin-right: 9px;
	}
}

@media screen and (max-width: 850px) and (max-height: 500px) {
	#conteneur h1 {
		font-size: 2em;
		margin-bottom: 1em;
	}

	#conteneur p {
		font-size: 1em;
		margin-bottom: 1.2em;
	}
	
	#bouton {
		font-size: 0.85em!important;
	}
	
	#credits p {
		font-size: 0.85em;
	}
}
</style>
