<template>
	<div id="page">
		<div id="parcours">
			<header>
				<div id="conteneur-header">
					<a id="conteneur-logo" :href="definirRacine()" :title="$t('accueil')">
						<span id="logo"></span>
					</a>
					<span id="titre">{{ nom }}</span>
					<span id="conteneur-partage" @click="afficherMenuPartager">
						<i class="material-icons">share_alt</i>
					</span>
					<span id="conteneur-parametres" @click="ouvrirModaleParcours" v-if="admin">
						<i class="material-icons">settings</i>
					</span>
					<span id="conteneur-parametres" @click="ouvrirModaleConnexion" v-else>
						<i class="material-icons">lock_open</i>
					</span>
				</div>
			</header>

			<div id="menu-partager" v-show="menu === 'partager'" :style="{'left': position + 'px'}">
				<div id="conteneur-partager">
					<label>{{ $t('lienEtCodeQR') }}</label>
					<div id="copier-lien" class="copier">
						<input type="text" disabled :value="definirRacine() + '#/s/' + id">
						<span class="icone lien" role="button" tabindex="0" :title="$t('copierLien')"><i class="material-icons">content_copy</i></span>
						<span class="icone codeqr" role="button" tabindex="0" :title="$t('afficherCodeQR')" @click="modale = 'code-qr'"><i class="material-icons">qr_code</i></span>
					</div>
					<label>{{ $t('codeIntegration') }}</label>
					<div id="copier-iframe" class="copier">
						<input type="text" disabled :value="'<iframe src=&quot;' + definirRacine() + '#/s/' + id + '&quot; allowfullscreen frameborder=&quot;0&quot; width=&quot;100%&quot; height=&quot;500&quot;></iframe>'">
						<span class="icone" role="button" tabindex="0" :title="$t('copierCode')"><i class="material-icons">content_copy</i></span>
					</div>
					<p class="credits">{{ $t('creeAvec') }}<a href="https://ladigitale.dev/digisteps/" target="_blank" rel="noreferrer"><u>Digisteps by La Digitale</u></a></p>
				</div>
			</div>

			<section>
				<div id="conteneur-parcours">
					<div id="actions" v-if="admin">
						<span id="ajouter" class="bouton" role="button" tabindex="0" @click="ouvrirModaleBloc('creation', '')">{{ $t('ajouterEtape') }}</span>
					</div>
					<draggable id="blocs" class="admin" v-model="blocs" :animation="250" :sort="true" :swap-threshold="0.5" :force-fallback="true" :fallback-tolerance="10" filter=".statique, .lire__link-wrap" :preventOnFilter="false" draggable=".bloc" @end="modifierPositionBloc" v-if="blocs.length > 0 && admin">
						<template v-for="(bloc, indexBloc) in blocs" :key="'bloc_' + indexBloc">
							<article :id="bloc.id" class="bloc section" :class="{'invisible': bloc.visibilite === false}" v-if="bloc.type === 'section'">
								<div class="contenu">
									<span class="titre" :style="{'border-color': bloc.couleur}">{{ bloc.titre }}</span>
									<span class="texte" v-if="bloc.texte !== ''" v-html="bloc.texte" />
								</div>
								<div class="actions statique">
									<span @click="modifierVisibiliteBloc(bloc.id)" :title="$t('masquer')" v-if="bloc.visibilite === true"><i class="material-icons">visibility</i></span>
									<span @click="modifierVisibiliteBloc(bloc.id)" :title="$t('afficher')" v-else><i class="material-icons">visibility_off</i></span>
									<span @click="ouvrirModaleBloc('edition', bloc)" :title="$t('editer')"><i class="material-icons">edit</i></span>
									<span @click="dupliquerBloc(bloc.id)" :title="$t('dupliquer')"><i class="material-icons">content_copy</i></span>
									<span @click="afficherSupprimerBloc(bloc.id)" :title="$t('supprimer')"><i class="material-icons">delete</i></span>
								</div>
							</article>
							<article :id="bloc.id" class="bloc" :class="{'invisible': bloc.visibilite === false}" :style="{'background': eclaircirCouleur(bloc.couleur)}" v-else>
								<div class="vignette" :style="{'border-color': bloc.couleur, 'color': modifierCouleur(bloc.couleur, -20)}">
									<span class="icone" v-if="bloc.vignette.substring(0, 5) === 'icone'"><i class="material-icons">{{ bloc.vignette.substring(6) }}</i></span>
									<span class="image" v-else><img :src="definirRacine() + 'static/vignettes/' + bloc.vignette + '.png'"></span>
									<span class="duree" v-if="bloc.heures > 0 || bloc.minutes > 0">{{ definirDuree(bloc.heures, bloc.minutes) }}</span>
									<span class="cadenas" v-if="bloc.hasOwnProperty('code') === true && bloc.code !== ''"><i class="material-icons">lock</i></span>
								</div>
								<div class="contenu" :class="bloc.type">
									<span class="titre" :style="{'border-color': bloc.couleur}">{{ bloc.titre }}</span>
									<span class="texte" v-if="bloc.texte !== ''" v-html="bloc.texte" />
									<div class="date-et-horaire" v-if="bloc.date !== '' && bloc.debut !== '' & bloc.fin !== ''">
										<span class="date"><i class="material-icons">event_note</i>{{ definirDate(bloc.date) }}</span>
										<span class="horaire"><i class="material-icons">schedule</i>{{ definirHoraire(bloc.date, bloc.debut, bloc.fin) }}</span>
									</div>
									<span class="lieu" v-if="bloc.lieu !== '' && bloc.lieu.includes('http') === false"><i class="material-icons">place</i><a :href="'https://www.openstreetmap.org/search?query=' + bloc.lieu" target="_blank">{{ bloc.lieu }}</a></span>
									<span class="lieu" v-else-if="bloc.lieu !== '' && bloc.lieu.includes('http') === true"><i class="material-icons">voice_chat</i><a :href="bloc.lieu" target="_blank">{{ definirDomaine(bloc.lieu) }}</a></span>
									<span class="image" v-if="bloc.fichier !== '' && verifierImage(bloc.fichier) === true"><img :src="definirRacine() + 'fichiers/' + id + '/vignette_' + bloc.fichier" @error="remplacerImage($event, bloc.fichier)"></span>
									<div class="action" v-if="bloc.lien !== '' || bloc.fichier !== '' || (bloc.hasOwnProperty('depot') === true && bloc.depot === 'oui' && bloc.hasOwnProperty('travaux') === true && bloc.travaux.length > 0) || (bloc.hasOwnProperty('listeCriteres') === true && bloc.listeCriteres.length > 0)">
										<a class="bouton icone" :href="bloc.lien" target="_blank" :title="$t('ouvrirLien')" :style="{'border-color': bloc.couleur, 'color': modifierCouleur(bloc.couleur, -20)}" v-if="bloc.lien !== ''"><i class="material-icons">open_in_new</i></a>
										<a class="bouton icone" :href="definirRacine() + 'fichiers/' + id + '/' + bloc.fichier" target="_blank" :title="$t('telechargerFichier')" :style="{'border-color': bloc.couleur, 'color': modifierCouleur(bloc.couleur, -20)}" v-else-if="bloc.fichier !== ''"><i class="material-icons">get_app</i></a>
										<span class="bouton icone" role="button" tabindex="0" :style="{'border-color': bloc.couleur, 'color': modifierCouleur(bloc.couleur, -20)}" v-if="bloc.hasOwnProperty('listeCriteres') === true && bloc.listeCriteres.length > 0" @click="ouvrirModaleCriteres(bloc.listeCriteres)"><i class="material-icons">fact_check</i></span>
										<span class="bouton icone travaux" role="button" tabindex="0" :title="$t('afficherTravaux')" :style="{'border-color': bloc.couleur}" v-if="bloc.hasOwnProperty('depot') === true && bloc.depot === 'oui' && bloc.hasOwnProperty('travaux') === true && bloc.travaux.length > 0" @click="ouvrirModaleTravaux(bloc)"><i class="material-icons">send</i></span>
									</div>
								</div>
								<div class="actions statique">
									<span @click="modifierVisibiliteBloc(bloc.id)" :title="$t('masquer')" v-if="bloc.visibilite === true"><i class="material-icons">visibility</i></span>
									<span @click="modifierVisibiliteBloc(bloc.id)" :title="$t('afficher')" v-else><i class="material-icons">visibility_off</i></span>
									<span @click="ouvrirModaleBloc('edition', bloc)" :title="$t('editer')"><i class="material-icons">edit</i></span>
									<span @click="dupliquerBloc(bloc.id)" :title="$t('dupliquer')"><i class="material-icons">content_copy</i></span>
									<span @click="afficherSupprimerBloc(bloc.id)" :title="$t('supprimer')"><i class="material-icons">delete</i></span>
								</div>
							</article>
						</template>
					</draggable>
					<div id="blocs" v-else-if="blocs.length > 0 && !admin">
						<template v-for="(bloc, indexBloc) in blocs" :key="'bloc_' + indexBloc">
							<article :id="bloc.id" class="bloc section" v-if="bloc.visibilite === true && bloc.type === 'section'">
								<div class="contenu">
									<span class="titre" :style="{'border-color': bloc.couleur}">{{ bloc.titre }}</span>
									<span class="texte" v-if="bloc.texte !== ''" v-html="bloc.texte" />
								</div>
							</article>
							<article :id="bloc.id" class="bloc" :class="{'verrouille': bloc.hasOwnProperty('code') === true && bloc.code !== ''}" :style="{'background': eclaircirCouleur(bloc.couleur)}" v-else-if="bloc.visibilite === true && bloc.type !== 'section'">
								<div class="vignette" :style="{'border-color': bloc.couleur, 'color': modifierCouleur(bloc.couleur, -20)}">
									<span class="icone" v-if="bloc.hasOwnProperty('code') === true && bloc.code !== ''"><i class="material-icons">lock</i></span>
									<span class="icone" v-else-if="(!bloc.hasOwnProperty('code') || bloc.code === '') && bloc.vignette.substring(0, 5) === 'icone'"><i class="material-icons">{{ bloc.vignette.substring(6) }}</i></span>
									<span class="image" v-else><img :src="definirRacine() + 'static/vignettes/' + bloc.vignette + '.png'"></span>
									<span class="duree" v-if="(!bloc.hasOwnProperty('code') || bloc.code === '') && (bloc.heures > 0 || bloc.minutes > 0)">{{ definirDuree(bloc.heures, bloc.minutes) }}</span>
								</div>
								<transition name="fondu">
									<div class="masque-chargement" v-if="chargement && bloc.id === blocId" :style="{'background': bloc.couleur}">
										<div class="conteneur-chargement">
											<div class="chargement" :style="{'border-top-color': modifierCouleur(bloc.couleur, -20)}" />
										</div>
									</div>
								</transition>
								<div class="contenu" :class="bloc.type" v-if="!bloc.hasOwnProperty('code') || bloc.code === ''">
									<span class="titre" :style="{'border-color': bloc.couleur}">{{ bloc.titre }}</span>
									<span class="texte" v-if="bloc.texte !== ''" v-html="bloc.texte" />
									<div class="date-et-horaire" v-if="bloc.date !== '' && bloc.debut !== '' && bloc.fin !== ''">
										<span class="date"><i class="material-icons">event_note</i>{{ definirDate(bloc.date) }}</span>
										<span class="horaire"><i class="material-icons">schedule</i>{{ definirHoraire(bloc.date, bloc.debut, bloc.fin) }}</span>
									</div>
									<span class="lieu" v-if="bloc.lieu !== '' && bloc.lieu.includes('http') === false"><i class="material-icons">place</i><a :href="'https://www.openstreetmap.org/search?query=' + bloc.lieu" target="_blank">{{ bloc.lieu }}</a></span>
									<span class="lieu" v-else-if="bloc.lieu !== '' && bloc.lieu.includes('http') === true"><i class="material-icons">voice_chat</i><a :href="bloc.lieu" target="_blank">{{ definirDomaine(bloc.lieu) }}</a></span>
									<span class="image" v-if="bloc.fichier !== '' && verifierImage(bloc.fichier) === true"><img :src="definirRacine() + 'fichiers/' + id + '/vignette_' + bloc.fichier" @error="remplacerImage($event, bloc.fichier)"></span>
									<div class="action" v-if="bloc.lien !== '' || bloc.fichier !== '' || (bloc.hasOwnProperty('depot') === true && bloc.depot === 'oui') || (bloc.hasOwnProperty('listeCriteres') === true && bloc.listeCriteres.length > 0)">
										<a class="bouton icone" :href="bloc.lien" target="_blank" :title="$t('ouvrirLien')" :style="{'border-color': bloc.couleur, 'color': modifierCouleur(bloc.couleur, -20)}" v-if="bloc.lien !== ''"><i class="material-icons">open_in_new</i></a>
										<a class="bouton icone" :href="definirRacine() + 'fichiers/' + id + '/' + bloc.fichier" target="_blank" :title="$t('telechargerFichier')" :style="{'border-color': bloc.couleur, 'color': modifierCouleur(bloc.couleur, -20)}" v-else-if="bloc.fichier !== ''"><i class="material-icons">get_app</i></a>
										<span class="bouton icone" role="button" tabindex="0" :style="{'border-color': bloc.couleur, 'color': modifierCouleur(bloc.couleur, -20)}" v-if="bloc.hasOwnProperty('listeCriteres') === true && bloc.listeCriteres.length > 0" @click="ouvrirModaleCriteres(bloc.listeCriteres)"><i class="material-icons">fact_check</i></span>
										<span class="bouton icone travaux" role="button" tabindex="0" :title="$t('deposerConsulterTravail')" :style="{'border-color': bloc.couleur}" v-if="bloc.hasOwnProperty('depot') === true && bloc.depot === 'oui'" @click="ouvrirModaleDepot(bloc.id)"><i class="material-icons">reply</i></span>
									</div>
								</div>
								<div class="contenu" v-else-if="bloc.hasOwnProperty('code') === true && bloc.code !== ''">
									<span class="titre" :style="{'border-color': bloc.couleur}">{{ bloc.titre }}</span>
									<label>{{ $t('contenuVerrouille') }}</label>
									<input type="text" :placeholder="$t('codeDebloquerContenu')" :style="{'border-color': bloc.couleur}" @keydown.enter="debloquerEtape(bloc.id)">
									<div class="action">
										<span class="bouton" role="button" tabindex="0" :style="{'border-color': bloc.couleur}" @click="debloquerEtape(bloc.id)">{{ $t('valider') }}</span>
										<span class="bouton icone" role="button" tabindex="0" :style="{'color': bloc.couleur}" @click="$parent.$parent.message = bloc.indice" v-if="bloc.indice !== ''"><i class="material-icons">info</i></span>
									</div>
								</div>
							</article>
						</template>
					</div>
					<div id="blocs" class="vide" v-else-if="blocs.length === 0 && $parent.$parent.chargement === false">
						<p>{{ $t('aucuneEtape') }}</p>
					</div>
				</div>
			</section>
		</div>

		<div class="conteneur-modale" v-if="modale === 'bloc'">
			<div id="bloc" class="modale">
				<header>
					<span class="titre" v-if="mode === 'creation' && type === '-'">{{ $t('ajouterEtape') }}</span>
					<span class="titre" v-else-if="mode === 'creation' && type === 'section'">{{ $t('ajouterSection') }}</span>
					<span class="titre" v-else-if="mode === 'creation' && type === 'seance'">{{ $t('ajouterSeance') }}</span>
					<span class="titre" v-else-if="mode === 'creation' && type === 'document'">{{ $t('ajouterDocument') }}</span>
					<span class="titre" v-else-if="mode === 'creation' && type === 'exercice'">{{ $t('ajouterExercice') }}</span>
					<span class="titre" v-else-if="mode === 'creation' && type === 'activite'">{{ $t('ajouterActivite') }}</span>
					<span class="titre" v-else>{{ $t('modifierEtape') }}</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleContenu"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur ascenseur">
					<div class="contenu">
						<label for="champ-titre">{{ $t('titre') }}</label>
						<input id="champ-titre" type="text" :placeholder="$t('ajouterTitre')" :value="titre" @input="titre = $event.target.value">
						<label v-if="type === '-' || type === 'section' || type === 'seance' || type === 'document'">{{ $t('description') }}</label>
						<label v-else>{{ $t('consigne') }}</label>
						<div id="texte" />
						<label>{{ $t('typeEtape') }}</label>
						<select @change="modifierType($event.target.value)">
							<option value="-" :selected="type === '-'">-</option>
							<option value="section" :selected="type === 'section'">-{{ $t('section') }}-</option>
							<option value="seance" :selected="type === 'seance'">{{ $t('seancePresenceDistance') }}</option>
							<option value="document" :selected="type === 'document'">{{ $t('documentFichierLien') }}</option>
							<option value="exercice" :selected="type === 'exercice'">{{ $t('exercice') }}</option>
							<option value="activite" :selected="type === 'activite'">{{ $t('activite') }}</option>
						</select>
						<div id="options" v-if="type !== '-' && type !== 'section'">
							<template v-if="type === 'seance'">
								<div id="date-et-horaire">
									<div id="date">
										<label for="champ-date">{{ $t('date') }}</label>
										<input id="champ-date" type="date" :value="date" @input="date = $event.target.value">
									</div>
									<div id="debut">
										<label for="champ-debut">{{ $t('debut') }}</label>
										<input id="champ-debut" type="time" :value="debut" @input="debut = $event.target.value">
									</div>
									<div id="fin">
										<label for="champ-fin">{{ $t('fin') }}</label>
										<input id="champ-fin" type="time" :value="fin" @input="fin = $event.target.value">
									</div>
								</div>
								<label for="champ-lieu">{{ $t('adresseLienVisio') }}</label>
								<input id="champ-lieu" type="text" :placeholder="$t('exemple') + '5 rue des fleurs, 2000 La Digitale ou https://meet.jit.si'" :value="lieu" @input="lieu = $event.target.value">
							</template>
							<template v-else-if="type === 'document' || type === 'exercice' || type === 'activite'">
								<label>{{ $t('typeRessource') }}</label>
								<select @change="modifierRessource($event.target.value)">
									<option value="-" :selected="ressource === '-'" v-if="type === 'activite'">-</option>
									<option value="lien" :selected="ressource === 'lien'">{{ $t('lien') }}</option>
									<option value="fichier" :selected="ressource === 'fichier'">{{ $t('fichier') }}</option>
								</select>
								<label for="champ-lien" v-if="ressource === 'lien'">{{ $t('lien') }}</label>
								<input id="champ-lien" type="text" :placeholder="$t('exemples') + 'https://ladigitale.dev/digiplay, https://ladigitale.dev/digiread'" :value="lien" @input="lien = $event.target.value" v-if="type === 'document' && ressource === 'lien'">
								<input id="champ-lien" type="text" :placeholder="$t('exemples') + 'https://digistorm.app, https://ladigitale.dev/digiquiz'" :value="lien" @input="lien = $event.target.value" v-else-if="type === 'exercice' && ressource === 'lien'">
								<input id="champ-lien" type="text" :placeholder="$t('exemples') + 'https://digipad.app, https://ladigitale.dev/digidoc'" :value="lien" @input="lien = $event.target.value" v-else-if="type === 'activite' && ressource === 'lien'">
								<label v-if="ressource === 'fichier'">{{ $t('fichierMax', { taille: 2 }) }}</label>
								<div id="fichier" v-if="ressource === 'fichier'">
									<a class="bouton" :href="definirRacine() + 'fichiers/' + id + '/' + fichier" target="_blank" v-if="mode === 'edition' && fichier !== '' && ancienFichier === fichier">{{ $t('voirFichierActuel') }}</a>
									<label for="televerser" class="bouton" role="button" tabindex="0" v-if="fichier === ''">{{ $t('selectionnerFichier') }}</label>
									<label for="televerser" class="bouton" role="button" tabindex="0" v-else-if="mode === 'creation' && fichier !== ''">{{ fichier }}</label>
									<label for="televerser" class="bouton" role="button" tabindex="0" v-else-if="mode === 'edition' && fichier !== '' && ancienFichier === fichier">{{ $t('remplacerFichier') }}</label>
									<label for="televerser" class="bouton" role="button" tabindex="0" v-else-if="mode === 'edition' && fichier !== '' && ancienFichier !== fichier">{{ fichier }}</label>
									<input id="televerser" type="file" accept=".jpg, .jpeg, .png, .gif, .mp3, .mp4, .pdf, .doc, .docx, .odt, .ppt, .pptx, .odp, .xls, .xlsx, .ods" @change="selectionnerFichier" style="display: none;">
								</div>
							</template>
							<template v-if="type === 'activite'">
								<label>{{ $t('depotLienFichier') }}</label>
								<div id="depot">
									<span class="label">
										<input type="radio" id="depot_oui" name="depot" value="oui" :checked="depot === 'oui'" @change="depot = $event.target.value">
										<label for="depot_oui">{{ $t('oui') }}</label>
									</span>
									<span class="label">
										<input type="radio" id="depot_non" name="depot" value="non" :checked="depot === 'non'" @change="depot = $event.target.value">
										<label for="depot_non">{{ $t('non') }}</label>
									</span>
								</div>
								<label>{{ $t('criteresEvaluation') }}</label>
								<div id="criteres">
									<span class="label">
										<input type="radio" id="criteres_oui" name="criteres" value="oui" :checked="criteres === 'oui'" @change="modifierCriteres($event.target.value)">
										<label for="criteres_oui">{{ $t('oui') }}</label>
									</span>
									<span class="label">
										<input type="radio" id="criteres_non" name="criteres" value="non" :checked="criteres === 'non'" @change="modifierCriteres($event.target.value)">
										<label for="criteres_non">{{ $t('non') }}</label>
									</span>
								</div>
								<div id="evaluation" v-if="criteres === 'oui'">
									<label>{{ $t('libelleCritere') }}</label>
									<label>{{ $t('nombreEtoiles') }}</label>
									<span class="critere" v-for="(critere, indexCritere) in listeCriteres" :key="'critere_' + indexCritere">
										<input type="text" :placeholder="$t('exempleCritere')" :value="critere.libelle" @input="critere.libelle = $event.target.value">
										<select @change="critere.etoiles = parseInt($event.target.value)">
											<option :value="1" :selected="critere.etoiles === 1">1</option>
											<option :value="2" :selected="critere.etoiles === 2">2</option>
											<option :value="3" :selected="critere.etoiles === 3">3</option>
											<option :value="4" :selected="critere.etoiles === 4">4</option>
											<option :value="5" :selected="critere.etoiles === 5">5</option>
											<option :value="6" :selected="critere.etoiles === 6">6</option>
											<option :value="7" :selected="critere.etoiles === 7">7</option>
											<option :value="8" :selected="critere.etoiles === 8">8</option>
											<option :value="9" :selected="critere.etoiles === 9">9</option>
											<option :value="10" :selected="critere.etoiles === 10">10</option>
										</select>
									</span>
									<div class="actions">
										<span role="button" tabindex="0" :title="$t('supprimerCritere')" @click="supprimerCritere"><i class="material-icons">remove_circle_outline</i></span>
										<span role="button" tabindex="0" :title="$t('ajouterCritere')" @click="ajouterCritere"><i class="material-icons">add_circle_outline</i></span>
									</div>
								</div>
							</template>
							<label v-if="type === 'seance'">{{ $t('duree') }}</label>
							<label v-else-if="type === 'document'">{{ $t('dureeEstimeeConsultation') }}</label>
							<label v-else>{{ $t('dureeEstimeeRealisation') }}</label>
							<div id="duree">
								<select @change="heures = parseInt($event.target.value)">
									<option :value="0" :selected="heures === 0">0</option>
									<option :value="1" :selected="heures === 1">1</option>
									<option :value="2" :selected="heures === 2">2</option>
									<option :value="3" :selected="heures === 3">3</option>
									<option :value="4" :selected="heures === 4">4</option>
									<option :value="5" :selected="heures === 5">5</option>
									<option :value="6" :selected="heures === 6">6</option>
									<option :value="7" :selected="heures === 7">7</option>
									<option :value="8" :selected="heures === 8">8</option>
									<option :value="9" :selected="heures === 9">9</option>
									<option :value="10" :selected="heures === 10">10</option>
									<option :value="11" :selected="heures === 11">11</option>
									<option :value="12" :selected="heures === 12">12</option>
								</select>
								<span>h</span>
								<select @change="minutes = parseInt($event.target.value)">
									<option :value="0" :selected="minutes === 0">00</option>
									<option :value="5" :selected="minutes === 5">05</option>
									<option :value="10" :selected="minutes === 10">10</option>
									<option :value="15" :selected="minutes === 15">15</option>
									<option :value="20" :selected="minutes === 20">20</option>
									<option :value="25" :selected="minutes === 25">25</option>
									<option :value="30" :selected="minutes === 30">30</option>
									<option :value="35" :selected="minutes === 35">35</option>
									<option :value="40" :selected="minutes === 40">40</option>
									<option :value="45" :selected="minutes === 45">45</option>
									<option :value="50" :selected="minutes === 50">50</option>
									<option :value="55" :selected="minutes === 55">55</option>
								</select>
							</div>
						</div>
						<template v-if="type !== '-' && type !== 'section'">
							<label>{{ $t('etapeVerrouilleeCode') }}</label>
							<div id="verrouillage">
								<span class="label">
									<input type="radio" id="verrouillage_oui" name="verrouillage" value="oui" :checked="verrouillage === 'oui'" @change="modifierVerrouillage($event.target.value)">
									<label for="verrouillage_oui">{{ $t('oui') }}</label>
								</span>
								<span class="label">
									<input type="radio" id="verrouillage_non" name="verrouillage" value="non" :checked="verrouillage === 'non'" @change="modifierVerrouillage($event.target.value)">
									<label for="verrouillage_non">{{ $t('non') }}</label>
								</span>
							</div>
							<label v-if="verrouillage === 'oui'">{{ $t('codeAcces') }}</label>
							<input id="champ-code" type="text" :placeholder="$t('ajouterCode')" :value="code" @input="code = $event.target.value" v-if="verrouillage === 'oui'">
							<label v-if="verrouillage === 'oui'">{{ $t('indiceCode') }}</label>
							<input id="champ-indice" type="text" :placeholder="$t('exempleIndice')" :value="indice" @input="indice = $event.target.value" v-if="verrouillage === 'oui'">
						</template>
						<label v-if="type !== '-' && type !== 'section'">{{ $t('vignette') }}</label>
						<select id="vignette" @change="modifierVignette($event.target.value)" v-if="type === 'seance'">
							<option value="icone_meeting_room" :selected="vignette === 'icone_meeting_room'">{{ $t('iconeClasse') }}</option>
							<option value="icone_devices" :selected="vignette === 'icone_devices'">{{ $t('iconeVisio') }}</option>
							<option value="jitsi" :selected="vignette === 'jitsi'">Jitsi Meet</option>
							<option value="teams" :selected="vignette === 'teams'">Microsoft Teams</option>
							<option value="zoom" :selected="vignette === 'zoom'">Zoom</option>
							<option value="meet" :selected="vignette === 'meet'">Google Meet</option>
						</select>
						<select id="vignette" @change="modifierVignette($event.target.value)" v-else-if="type === 'document'">
							<option value="icone_article" :selected="vignette === 'icone_article'">{{ $t('iconeDocument') }}</option>
							<option value="icone_cloud_download" :selected="vignette === 'icone_cloud_download'">{{ $t('iconeNuage') }}</option>
							<option value="icone_explore" :selected="vignette === 'icone_explore'">{{ $t('iconeBoussole') }}</option>
							<option value="ladigitale" :selected="vignette === 'ladigitale'">La Digitale</option>
							<option value="wikipedia" :selected="vignette === 'wikipedia'">Wikipedia</option>
							<option value="canoprof" :selected="vignette === 'canoprof'">Canoprof</option>
							<option value="tv5monde" :selected="vignette === 'tv5monde'">TV5MONDE</option>
							<option value="lumni" :selected="vignette === 'lumni'">Lumni</option>
							<option value="peertube" :selected="vignette === 'peertube'">Peertube</option>
							<option value="pdf" :selected="vignette === 'pdf'">{{ $t('fichierPDF') }}</option>
							<option value="odt" :selected="vignette === 'odt'">LibreOffice Writer</option>
							<option value="odp" :selected="vignette === 'odp'">LibreOffice Impress</option>
							<option value="ods" :selected="vignette === 'ods'">LibreOffice Calc</option>
							<option value="doc" :selected="vignette === 'doc'">Microsoft Word</option>
							<option value="ppt" :selected="vignette === 'ppt'">Microsoft Powerpoint</option>
							<option value="xls" :selected="vignette === 'xls'">Microsoft Excel</option>
							<option value="gdoc" :selected="vignette === 'gdoc'">Google Document</option>
							<option value="gslides" :selected="vignette === 'gslides'">Google Slides</option>
							<option value="gsheets" :selected="vignette === 'gsheets'">Google Sheets</option>
							<option value="genially" :selected="vignette === 'genially'">Genially</option>
							<option value="youtube" :selected="vignette === 'youtube'">Youtube</option>
							<option value="vimeo" :selected="vignette === 'vimeo'">Vimeo</option>
						</select>
						<select id="vignette" @change="modifierVignette($event.target.value)" v-else-if="type === 'exercice'">
							<option value="icone_check_circle" :selected="vignette === 'icone_check_circle'">{{ $t('iconeExercice') }}</option>
							<option value="ladigitale" :selected="vignette === 'ladigitale'">La Digitale</option>
							<option value="h5p" :selected="vignette === 'h5p'">H5P</option>
							<option value="learningapps" :selected="vignette === 'learningapps'">Learning Apps</option>
							<option value="snacks" :selected="vignette === 'snacks'">Learning Snacks</option>
							<option value="quizlet" :selected="vignette === 'quizlet'">Quizlet</option>
						</select>
						<select id="vignette" @change="modifierVignette($event.target.value)" v-else-if="type === 'activite'">
							<option value="icone_lightbulb" :selected="vignette === 'icone_lightbulb'">{{ $t('iconeActivite') }}</option>
							<option value="icone_assessment" :selected="vignette === 'icone_assessment'">{{ $t('iconeEvaluation') }}</option>
							<option value="icone_check_circle" :selected="vignette === 'icone_check_circle'">{{ $t('iconeCoche') }}</option>
							<option value="ladigitale" :selected="vignette === 'ladigitale'">La Digitale</option>
							<option value="h5p" :selected="vignette === 'h5p'">H5P</option>
							<option value="quiziniere" :selected="vignette === 'quiziniere'">QuiZinière</option>
							<option value="vocaroo" :selected="vignette === 'vocaroo'">Vocaroo</option>
							<option value="flipgrid" :selected="vignette === 'flipgrid'">Flipgrid</option>
							<option value="genially" :selected="vignette === 'genially'">Genially</option>
						</select>
						<div id="apercu" v-if="type !== '-' && type !== 'section'">
							<i class="material-icons" v-if="vignette.substring(0, 5) === 'icone'">{{ vignette.substring(6) }}</i>
							<img :src="definirRacine() + 'static/vignettes/' + vignette + '.png'" v-else>
						</div>
						<label>{{ $t('couleur') }}</label>
						<div id="couleurs">
							<span v-for="(item, index) in couleurs" :class="{'selectionne': item === couleur}" :style="{'background': item}" @click="selectionnerCouleur(item)" :key="'couleur_' + index" />
							<span class="icone">
								<label for="couleur"><i class="material-icons">colorize</i></label>
								<input type="color" id="couleur" value="#dddddd" :title="$t('selectionnerCouleur')" @change="selectionnerCouleur($event.target.value)" v-if="couleurs.includes(couleur)">
								<input type="color" id="couleur" class="selectionne" :title="$t('selectionnerCouleur')" @change="selectionnerCouleur($event.target.value)" v-else>
							</span>
						</div>
					</div>
				</div>
				<div id="valider" role="button" tabindex="0" @click="ajouterBloc" v-if="mode === 'creation'">{{ $t('valider') }}</div>
				<div id="valider" role="button" tabindex="0" @click="modifierBloc" v-else>{{ $t('modifier') }}</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'televerser'">
			<div class="modale">
				<header>
					<span class="titre">{{ $t('televersementFichier') }}</span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<div class="conteneur-chargement progression">
							<progress class="barre-progression" max="100" :value="progression" />
							<div class="chargement" />
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'supprimer-bloc'">
			<div class="modale confirmation">
				<div class="conteneur entier">
					<div class="contenu">
						<p v-html="$t('confirmationSupprimerEtape')" />
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="modale = ''">{{ $t('non') }}</span>
							<span class="bouton" role="button" tabindex="0" @click="supprimerBloc">{{ $t('oui') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'travaux'">
			<div id="travaux" class="modale">
				<header>
					<span class="titre">{{ $t('afficherTravaux') }}</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleTravaux"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur ascenseur">
					<div class="contenu">
						<div :id="'travail' + travail.motdepasse" class="travail" v-for="(travail, indexTravail) in travaux" :key="'travail_' + indexTravail">
							<span class="meta">{{ $t('par') }}<b>{{ travail.pseudo }}</b> {{ definirDateEtHeure(travail.date) }} <span class="motdepasse" v-html="'(' + $t('motDePasse') + travail.motdepasse + ')'" /></span>
							<div class="boutons">
								<a class="bouton icone" :href="travail.lien" target="_blank" :title="$t('ouvrirLienTravail')" v-if="travail.lien !== ''"><i class="material-icons">open_in_new</i></a>
								<a class="bouton icone" :href="definirRacine() + 'fichiers/' + id + '/' + travail.fichier" target="_blank" :title="$t('telerchargerTravail')" v-else-if="travail.fichier !== ''"><i class="material-icons">get_app</i></a>
								<span class="bouton icone" role="button" tabindex="0" :title="$t('fermerEvaluation')" @click="afficherRetroaction(travail)" v-if="parseInt(travail.motdepasse) === parseInt(motdepasseRetroaction) && travail.retroaction === ''"><i class="material-icons">speaker_notes_off</i></span>
								<span class="bouton icone" role="button" tabindex="0" :title="$t('evaluer')" @click="afficherRetroaction(travail)" v-else-if="parseInt(travail.motdepasse) !== parseInt(motdepasseRetroaction) && travail.retroaction === ''"><i class="material-icons">speaker_notes</i></span>
								<span class="bouton icone evaluer" role="button" tabindex="0" :title="$t('fermerEvaluation')" @click="afficherRetroaction(travail)" v-else-if="parseInt(travail.motdepasse) === parseInt(motdepasseRetroaction) && travail.retroaction !== ''"><i class="material-icons">edit_off</i></span>
								<span class="bouton icone evaluer" role="button" tabindex="0" :title="$t('modifierEvaluation')" @click="afficherRetroaction(travail)" v-else-if="parseInt(travail.motdepasse) !== parseInt(motdepasseRetroaction) && travail.retroaction !== ''"><i class="material-icons">edit</i></span>
								<span class="bouton icone rouge" :title="$t('supprimerTravail')" @click="afficherSupprimerTravail(travail.motdepasse)"><i class="material-icons">delete</i></span>
							</div>
							<div class="retroaction" v-if="parseInt(travail.motdepasse) === parseInt(motdepasseRetroaction)">
								<div id="liste-criteres" v-if="listeCriteres.length > 0">
									<template v-for="(critere, indexCritere) in listeCriteres" :key="'critere_' + indexCritere">
										<label>{{ critere.libelle }}</label>
										<div class="etoiles">
											<span class="etoile" v-for="etoile in evaluation[indexCritere]" @click="evaluation[indexCritere] = etoile" :key="'etoile_' + etoile"><i class="material-icons">star</i></span>
											<span class="etoile" v-for="etoile in (critere.etoiles - evaluation[indexCritere])" @click="evaluation[indexCritere] = evaluation[indexCritere] + etoile" :key="'etoile_' + etoile"><i class="material-icons">star_outline</i></span>
										</div>
									</template>
								</div>
								<div id="retroaction" />
								<div class="boutons">
									<span class="bouton" role="button" tabindex="0" @click="annulerRetroaction">{{ $t('annuler') }}</span>
									<span class="bouton" role="button" tabindex="0" @click="enregistrerRetroaction">{{ $t('enregistrer') }}</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="valider" role="button" tabindex="0" @click="deposer" v-if="mode === 'deposer' || mode === 'afficher'">{{ $t('valider') }}</div>
				<div id="valider" role="button" tabindex="0" @click="verifier" v-else-if="mode === 'verifier'">{{ $t('valider') }}</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'depot'">
			<div id="travail" class="modale" :class="{'deposer': mode === 'deposer' || mode === 'afficher', 'verifier': mode === 'verifier'}">
				<header>
					<span class="titre" v-if="mode === 'deposer'">{{ $t('deposerTravail') }}</span>
					<span class="titre" v-else-if="mode === 'verifier' || mode === 'afficher'">{{ $t('consulterTravail') }}</span>
					<span class="titre" v-else>{{ $t('deposerConsulterTravail') }}</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleDepot"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur ascenseur">
					<div class="contenu">
						<label v-if="mode === '' || mode === '-'">{{ $t('jeVeux') }}</label>
						<select @change="modifierModeDepot($event.target.value)" v-if="mode === '' || mode === '-'">
							<option value="-">-</option>
							<option value="deposer">{{ $t('deposerTravailMin') }}</option>
							<option value="verifier">{{ $t('consulterTravailMin') }}</option>
						</select>
						<template v-if="mode === 'deposer'">
							<label>{{ $t('nomOuPseudo') }}</label>
							<input type="text" :value="pseudo" :placeholder="$t('nomOuPseudo')"  @input="pseudo = $event.target.value">
							<label v-html="$t('remarqueMotDePasse')" />
							<input type="text" :value="motdepasse" disabled>
							<label>{{ $t('typeDepot') }}</label>
							<select @change="modifierRessourceDepot($event.target.value)">
								<option value="lien" :selected="ressource === 'lien'">{{ $t('lien') }}</option>
								<option value="fichier" :selected="ressource === 'fichier'">{{ $t('fichier') }}</option>
							</select>
							<label for="champ-lien" v-if="ressource === 'lien'">{{ $t('lien') }}</label>
							<input id="champ-lien" type="text" :value="lien" placeholder="https://..." @input="lien = $event.target.value" v-if="ressource === 'lien'">
							<label v-if="ressource === 'fichier'">{{ $t('fichierMax', { taille: 2 }) }}</label>
							<div id="fichier" v-if="ressource === 'fichier'">
								<label for="televerser" class="bouton" role="button" tabindex="0" v-if="fichier === ''">{{ $t('selectionnerFichier') }}</label>
								<label for="televerser" class="bouton" role="button" tabindex="0" v-else>{{ fichier }}</label>
								<input id="televerser" type="file" accept=".jpg, .jpeg, .png, .gif, .mp3, .mp4, .pdf, .doc, .docx, .odt, .ppt, .pptx, .odp, .xls, .xlsx, .ods" @change="selectionnerFichier" style="display: none;">
							</div>
						</template>
						<template v-else-if="mode === 'verifier'">
							<label>{{ $t('motdepasse') }}</label>
							<input type="text" :value="motdepasse" placeholder="6 chiffres" @input="motdepasse = $event.target.value" @keydown.enter="verifier">
						</template>
						<template v-else-if="mode === 'afficher'">
							<label>{{ $t('nomOuPseudo') }}</label>
							<input type="text" :value="pseudo" :placeholder="$t('nomOuPseudo')" @input="pseudo = $event.target.value">
							<label>{{ $t('motdepasse') }}</label>
							<input type="text" :value="motdepasse" disabled>
							<label>{{ $t('typeDepot') }}</label>
							<select @change="modifierRessourceDepot($event.target.value)">
								<option value="lien" :selected="ressource === 'lien'">{{ $t('lien') }}</option>
								<option value="fichier" :selected="ressource === 'fichier'">{{ $t('fichier') }}</option>
							</select>
							<label for="champ-lien" v-if="ressource === 'lien'">{{ $t('lien') }}</label>
							<input id="champ-lien" type="text" :value="lien" placeholder="https://..." @input="lien = $event.target.value" v-if="ressource === 'lien'">
							<label v-if="ressource === 'fichier'">{{ $t('fichierMax', { taille: 2 }) }}</label>
							<div id="fichier" v-if="ressource === 'fichier'">
								<a class="bouton" :href="definirRacine() + 'fichiers/' + id + '/' + fichier" target="_blank" v-if="fichier !== '' && ancienFichier === fichier">{{ $t('voirFichierActuel') }}</a>
								<label for="televerser" class="bouton" role="button" tabindex="0" v-if="fichier === ''">{{ $t('selectionnerFichier') }}</label>
								<label for="televerser" class="bouton" role="button" tabindex="0" v-else-if="fichier !== '' && ancienFichier === fichier">{{ $t('remplacerFichier') }}</label>
								<label for="televerser" class="bouton" role="button" tabindex="0" v-else-if="fichier !== '' && ancienFichier !== fichier">{{ fichier }}</label>
								<input id="televerser" type="file" accept=".jpg, .jpeg, .png, .gif, .mp3, .mp4, .pdf, .doc, .docx, .odt, .ppt, .pptx, .odp, .xls, .xlsx, .ods" @change="selectionnerFichier" style="display: none;">
							</div>
							<label>{{ $t('commentaire') }}</label>
							<div id="commentaire" v-if="retroaction !== ''" v-html="retroaction" />
							<div id="commentaire" v-else>{{ $t('aucunCommentaire') }}</div>
							<div id="etoiles" v-if="evaluation.length > 0">
								<template v-for="(critere, indexCritere) in listeCriteres" :key="'critere_' + indexCritere">
									<label>{{ critere.libelle }}</label>
									<div class="etoiles">
										<span class="etoile" v-for="etoile in evaluation[indexCritere]" :key="'etoile_' + etoile"><i class="material-icons">star</i></span>
										<span class="etoile" v-for="etoile in (critere.etoiles - evaluation[indexCritere])" :key="'etoile_' + etoile"><i class="material-icons">star_outline</i></span>
									</div>
								</template>
							</div>
							<span class="bouton large rouge" role="button" tabindex="0" v-if="retroaction === '' && evaluation.length === 0" @click="afficherSupprimerDepot">{{ $t('supprimerTravail') }}</span>
						</template>
					</div>
				</div>
				<div id="valider" role="button" tabindex="0" @click="deposer" v-if="mode === 'deposer' || mode === 'afficher'">{{ $t('valider') }}</div>
				<div id="valider" role="button" tabindex="0" @click="verifier" v-else-if="mode === 'verifier'">{{ $t('valider') }}</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'criteres'">
			<div class="modale">
				<header>
					<span class="titre">{{ $t('criteresEvaluation') }}</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleCriteres"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<template v-for="(critere, indexCritere) in listeCriteres" :key="'critere_' + indexCritere">
							<label>{{ critere.libelle }}</label>
							<div class="etoiles">
								<span class="etoile" v-for="etoile in critere.etoiles" :key="'etoile_' + etoile"><i class="material-icons">star</i></span>
							</div>
						</template>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'connexion'">
			<div class="modale">
				<header>
					<span class="titre">{{ $t('debloquerParcours') }}</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleConnexion"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<div class="langue">
							<span :class="{'selectionne': $parent.$parent.langue === 'fr'}" @click="modifierLangue('fr')">FR</span>
							<span :class="{'selectionne': $parent.$parent.langue === 'it'}" @click="modifierLangue('it')">IT</span>
							<span :class="{'selectionne': $parent.$parent.langue === 'en'}" @click="modifierLangue('en')">EN</span>
						</div>
						<label>{{ $t('questionSecrete') }}</label>
						<select :value="question" @change="question = $event.target.value">
							<option v-for="(item, index) in questions" :value="item" :key="'option_' + index">{{ $t(item) }}</option>
						</select>
						<label>{{ $t('reponseSecrete') }}</label>
						<input type="password" :value="reponse" @input="reponse = $event.target.value" @keydown.enter="debloquerParcours">
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="debloquerParcours">{{ $t('valider') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'parcours'">
			<div id="modale-parametres" class="modale">
				<header>
					<span class="titre">{{ $t('parametresParcours') }}</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleParcours"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<div class="langue">
							<span :class="{'selectionne': $parent.$parent.langue === 'fr'}" @click="modifierLangue('fr')">FR</span>
							<span :class="{'selectionne': $parent.$parent.langue === 'it'}" @click="modifierLangue('it')">IT</span>
							<span :class="{'selectionne': $parent.$parent.langue === 'en'}" @click="modifierLangue('en')">EN</span>
						</div>
						<span class="bouton large" role="button" tabindex="0" @click="ouvrirModaleNomParcours">{{ $t('modifierNomParcours') }}</span>
						<span class="bouton large" role="button" tabindex="0" @click="ouvrirModaleAccesParcours">{{ $t('modifierAccesParcours') }}</span>
						<span class="bouton large" role="button" tabindex="0" @click="exporterParcours">{{ $t('exporterParcours') }}</span>
						<span class="bouton large" role="button" tabindex="0" @click="modale = 'importer'">{{ $t('importerParcours') }}</span>
						<span class="bouton large rouge" role="button" tabindex="0" @click="afficherSupprimerParcours">{{ $t('supprimerParcours') }}</span>
						<span class="bouton large" role="button" tabindex="0" @click="terminerSession">{{ $t('terminerSession') }}</span>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'importer'">
			<div class="modale">
				<header>
					<span class="titre">{{ $t('importerParcours') }}</span>
					<span class="fermer" role="button" tabindex="0" @click="modale = ''"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<p>{{ $t('alerteImporter') }}</p>
						<input type="file" id="importer" name="importer" style="display: none;" accept=".zip" @change="importerParcours">
						<label for="importer" class="bouton large">{{ $t('selectionnerFichierImport') }}</label>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'modifier-nom'">
			<div class="modale">
				<header>
					<span class="titre">{{ $t('modifierNomParcours') }}</span>
					<span class="fermer" role="button" @click="fermerModaleNomParcours"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<label>{{ $t('nouveauNom') }}</label>
						<input type="text" :value="nouveaunom" @input="nouveaunom = $event.target.value" @keydown.enter="modifierNomParcours">
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="modifierNomParcours">{{ $t('modifier') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'modifier-acces'">
			<div class="modale">
				<header>
					<span class="titre">{{ $t('modifierAccesParcours') }}</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleAccesParcours"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<label>{{ $t('questionSecreteActuelle') }}</label>
						<select :value="question" @change="question = $event.target.value">
							<option v-for="(item, index) in questions" :value="item" :key="'option_' + index">{{ $t(item) }}</option>
						</select>
						<label>{{ $t('reponseSecreteActuelle') }}</label>
						<input type="text" :value="reponse" @input="reponse = $event.target.value">
						<label>{{ $t('nouvelleQuestionSecrete') }}</label>
						<select :value="nouvellequestion" @change="nouvellequestion = $event.target.value">
							<option v-for="(item, index) in questions" :value="item" :key="'option_' + index">{{ $t(item) }}</option>
						</select>
						<label>{{ $t('nouvelleReponseSecrete') }}</label>
						<input type="text" :value="nouvellereponse" @input="nouvellereponse = $event.target.value" @keydown.enter="modifierAccesParcours">
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="modifierAccesParcours">{{ $t('modifier') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-if="confirmation === 'supprimer-parcours'">
			<div class="modale confirmation">
				<div class="conteneur entier">
					<div class="contenu">
						<p v-html="$t('confirmationSupprimerParcours')" />
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="confirmation = ''">{{ $t('non') }}</span>
							<span class="bouton" role="button" tabindex="0" @click="supprimerParcours">{{ $t('oui') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="confirmation === 'supprimer-travail'">
			<div class="modale confirmation">
				<div class="conteneur entier">
					<div class="contenu">
						<p v-html="$t('confirmationSupprimerTravail')" />
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="annulerSupprimerTravail">{{ $t('non') }}</span>
							<span class="bouton" role="button" tabindex="0" @click="supprimerTravail">{{ $t('oui') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="confirmation === 'supprimer-depot'">
			<div class="modale confirmation">
				<div class="conteneur entier">
					<div class="contenu">
						<p v-html="$t('confirmationSupprimerTravail')" />
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="confirmation = ''">{{ $t('non') }}</span>
							<span class="bouton" role="button" tabindex="0" @click="supprimerDepot">{{ $t('oui') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-show="modale === 'code-qr'">
			<div id="codeqr" class="modale">
				<header>
					<span class="titre">{{ $t('codeQR') }}</span>
					<span class="fermer" role="button" tabindex="0" @click="modale = ''"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<div id="qr" />
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import ClipboardJS from 'clipboard'
import pell from 'pell'
import linkifyHtml from 'linkify-html'
import eol from 'eol'
import ReadSmore from 'read-smore'
import moment from 'moment'
import imagesLoaded from 'imagesloaded'
import { saveAs } from 'file-saver'
import latinise from 'voca/latinise'
import JSZip from 'jszip'
import { VueDraggableNext } from 'vue-draggable-next'

export default {
	name: 'Editeur',
	components: {
		draggable: VueDraggableNext
	},
	data () {
		return {
			modale: '',
			menu: '',
			confirmation: '',
			admin: false,
			id: '',
			question: '',
			questions: ['motPrefere', 'filmPrefere', 'chansonPreferee', 'prenomMere', 'prenomPere', 'nomRue', 'nomEmployeur', 'nomAnimal'],
			reponse: '',
			nom: '',
			nouveaunom: '',
			nouvellequestion: '',
			nouvellereponse: '',
			blocs: [],
			mode: 'creation',
			blocId: '',
			titre: '',
			texte: '',
			type: '-',
			date: '',
			debut: '',
			fin: '',
			lieu: '',
			ressource: '-',
			lien: '',
			fichier: '',
			ancienFichier: '',
			depot: 'non',
			travaux: [],
			retroaction: '',
			motdepasseRetroaction: '',
			motdepasseTravail: '',
			evaluation: [],
			criteres: 'non',
			listeCriteres: [],
			verrouillage: 'non',
			code: '',
			indice: '',
			vignette: '',
			heures: 0,
			minutes: 0,
			couleur: '#00ced1',
			couleurs: ['#00ced1', '#55efc4', '#74b9ff', '#a29bfe', '#ffeaa7', '#fab1a0', '#fea7c6'],
			pseudo: '',
			motdepasse: '',
			chargement: false,
			codeqr: '',
			position: 0,
			progression: 0
		}
	},
	watch: {
		modale: function (valeur) {
			if (valeur !== '') {
				this.menu = ''
			}
		}
	},
	created () {
		this.id = this.$route.params.id
		const langue = this.$route.query.lang
		if (this.$parent.$parent.langues.includes(langue) === true) {
			this.$parent.$parent.langue = langue
			localStorage.setItem('digisteps_lang', langue)
		}
		this.$parent.$parent.chargement = false
		const xhr = new XMLHttpRequest()
		xhr.onload = function () {
			if (xhr.readyState === xhr.DONE && xhr.status === 200) {
				if (this.verifierJSON(xhr.responseText) === false) {
					this.$router.push('/')
					return false
				}
				const reponse = JSON.parse(xhr.responseText)
				if (!reponse.nom || reponse.nom === '') {
					this.$router.push('/')
					return false
				}
				this.admin = reponse.admin
				this.nom = reponse.nom
				if (reponse.donnees !== '') {
					const donnees = JSON.parse(reponse.donnees)
					donnees.blocs.forEach(function (bloc, index) {
						if (!bloc || !bloc.hasOwnProperty('id')) {
							donnees.blocs.splice(index, 1)
						}
					})
					this.blocs = donnees.blocs
				}
				setTimeout(function () {
					document.title = this.nom + ' - Digisteps by La Digitale'
					this.verifierTextes()
					imagesLoaded('#blocs', { background: true }, function () {
						this.$parent.$parent.chargementParcours = false
					}.bind(this))
				}.bind(this), 300)
			} else {
				this.$parent.$parent.chargementParcours = false
				this.$parent.$parent.message = this.$t('erreurServeur')
			}
		}.bind(this)
		xhr.open('POST', this.$parent.$parent.hote + 'inc/recuperer_parcours.php', true)
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
		xhr.send('id=' + this.id)
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
		const lien = this.definirRacine() + '#/s/' + this.id
		const clipboardLien = new ClipboardJS('#copier-lien .lien', {
			text: function () {
				return lien
			}
		})
		clipboardLien.on('success', function () {
			this.$parent.$parent.notification = this.$t('lienCopie')
		}.bind(this))
		const iframe = '<iframe src="' + lien + '" allowfullscreen frameborder="0" width="100%" height="500"></iframe>'
		const clipboardIframe = new ClipboardJS('#copier-iframe span', {
			text: function () {
				return iframe
			}
		})
		clipboardIframe.on('success', function () {
			this.$parent.$parent.notification = this.$t('codeCopie')
		}.bind(this))
		// eslint-disable-next-line
		this.codeqr = new QRCode('qr', {
			text: lien,
			width: 360,
			height: 360,
			colorDark: '#000000',
			colorLight: '#ffffff',
			// eslint-disable-next-line
			correctLevel : QRCode.CorrectLevel.H
		})

		document.addEventListener('click', function (event) {
			const partager = document.querySelector('#conteneur-partage')
			const menuPartager = document.querySelector('#menu-partager')
			if (partager && menuPartager && event.target !== partager && event.target !== menuPartager && !partager.contains(event.target) && !menuPartager.contains(event.target)) {
				this.menu = ''
			}
		}.bind(this))

		window.addEventListener('resize', function () {
			if (this.menu === 'partager') {
				this.menu = ''
			}
		}.bind(this))
	},
	methods: {
		definirRacine () {
			return window.location.href.split('#')[0]
		},
		definirDuree (heures, minutes) {
			if (heures === 0) {
				return minutes + ' min.'
			} else if (heures > 0 && minutes === 0 || heures > 0 && minutes === 5) {
				return heures + ' h 0' + minutes
			} else {
				return heures + ' h ' + minutes
			}
		},
		definirDate (date) {
			return 'le ' + moment(new Date(date)).locale('fr').format('L')
		},
		definirHoraire (date, debut, fin) {
			return 'de ' + moment(new Date(date + ' ' + debut)).locale('fr').format('LT') + ' à ' + moment(new Date(date + ' ' + fin)).locale('fr').format('LT') 
		},
		definirDateEtHeure (date) {
			return 'le ' + moment(new Date(date)).locale('fr').format('L') + ' à ' + moment(new Date(date)).locale('fr').format('LT')
		},
		definirDomaine (url) {
			return (new URL(url)).hostname
		},
		modifierLangue (langue) {
			this.$root.$i18n.locale = langue
			this.$parent.$parent.langue = langue
			document.getElementsByTagName('html')[0].setAttribute('lang', langue)
			this.$parent.$parent.notification = this.$t('langueModifiee')
			localStorage.setItem('digisteps_lang', langue)
		},
		verifierJSON (json) {
			try {
				JSON.parse(json)
				return true
			} catch {
				return false
			}
		},
		verifierTextes () {
			const textes = document.querySelectorAll('.texte')
			const options = {
				blockClassName: 'lire',
				moreText: this.$t('lireSuite'),
				lessText: this.$t('reduire'),
				wordsCount: 20
			}
			// eslint-disable-next-line
			ReadSmore(textes, options).destroy()
			// eslint-disable-next-line
			ReadSmore(textes, options).init()
		},
		afficherMenuPartager () {
			this.menu = 'partager'
			this.$nextTick(function () {
				const rect = document.querySelector("#conteneur-partage").getBoundingClientRect()
				const largeurBouton = rect.width
				const largeurMenu = document.querySelector("#menu-partager").getBoundingClientRect().width
				this.position = rect.left - ((largeurMenu * 90) / 100 - largeurBouton / 2)
			}.bind(this))
		},
		ouvrirModaleBloc (mode, item) {
			this.mode = mode
			if (mode === 'edition') {
				this.blocId = item.id
				this.titre = item.titre
				this.texte = item.texte
				this.type = item.type
				this.date = item.date
				this.debut = item.debut
				this.fin = item.fin
				this.lieu = item.lieu
				this.lien = item.lien
				this.fichier = item.fichier
				this.ancienFichier = item.fichier
				if (this.lien !== '') {
					this.ressource = 'lien'
				} else if (this.fichier !== '') {
					this.ressource = 'fichier'
				}
				if (this.type === 'evaluation') {
					this.type = 'activite'
				}
				if (item.hasOwnProperty('depot') === true) {
					this.depot = item.depot
				}
				if (item.hasOwnProperty('travaux') === true) {
					this.travaux = item.travaux
				}
				if (item.hasOwnProperty('criteres') === true) {
					this.criteres = item.criteres
				}
				if (item.hasOwnProperty('listeCriteres') === true) {
					this.listeCriteres = item.listeCriteres
				}
				if (item.hasOwnProperty('verrouillage') === true) {
					this.verrouillage = item.verrouillage
				}
				if (item.hasOwnProperty('code') === true) {
					this.code = item.code
				}
				if (item.hasOwnProperty('indice') === true) {
					this.indice = item.indice
				}
				this.vignette = item.vignette
				this.heures = item.heures
				this.minutes = item.minutes
				this.couleur = item.couleur
			}
			this.modale = 'bloc'
			this.$nextTick(function () {
				if (mode === 'creation') {
					document.querySelector('#champ-titre').focus()
				} else if (mode === 'edition' && !this.couleurs.includes(item.couleur)) {
					this.$nextTick(function () {
						document.querySelector('#couleur').value = item.couleur
					})
				}
				this.chargerEditeur()
			})
		},
		chargerEditeur () {
			document.querySelector('#texte').innerHTML = ''
			const that = this
			const editeur = pell.init({
				element: document.querySelector('#texte'),
				onChange: function (html) {
					let texte = html.replace(/(<a [^>]*)(target="[^"]*")([^>]*>)/gi, '$1$3')
					texte = texte.replace(/(<a [^>]*)(>)/gi, '$1 target="_blank"$2')
					texte = linkifyHtml(texte, {
						defaultProtocol: 'https'
					})
					this.texte = texte
				}.bind(this),
				actions: [
					{ name: 'gras', title: that.$t('gras'), icon: '<i class="material-icons">format_bold</i>', result: () => pell.exec('bold') },
					{ name: 'italique', title: that.$t('italique'), icon: '<i class="material-icons">format_italic</i>', result: () => pell.exec('italic') },
					{ name: 'souligne', title: that.$t('souligne'), icon: '<i class="material-icons">format_underlined</i>', result: () => pell.exec('underline') },
					{ name: 'barre', title: that.$t('barre'), icon: '<i class="material-icons">format_strikethrough</i>', result: () => pell.exec('strikethrough') },
					{ name: 'listeordonnee', title: that.$t('listeOrdonnee'), icon: '<i class="material-icons">format_list_numbered</i>', result: () => pell.exec('insertOrderedList') },
					{ name: 'liste', title: that.$t('liste'), icon: '<i class="material-icons">format_list_bulleted</i>', result: () => pell.exec('insertUnorderedList') },
					{ name: 'couleur', title: that.$t('couleurTexte'), icon: '<label for="couleur-texte"><i class="material-icons">format_color_text</i></label><input id="couleur-texte" type="color">', result: () => undefined },
					{ name: 'lien', title: that.$t('lien'), icon: '<i class="material-icons">link</i>', result: () => { const url = window.prompt(that.$t('adresseLien')); if (url) { pell.exec('createLink', url) } } }
				],
				classes: { actionbar: 'boutons-editeur', button: 'bouton-editeur', content: 'contenu-editeur', selected: 'bouton-actif' }
			})
			editeur.content.innerHTML = this.texte
			editeur.onpaste = function (event) {
				event.preventDefault()
				event.stopPropagation()
				const texte = event.clipboardData.getData('text/plain')
				const lignes = eol.split(texte)
				let html = ''
				lignes.forEach(function (ligne) {
					html += '<div>' + ligne + '</div>'
				})
				pell.exec('insertHtml', html)
			}
			document.querySelector('#texte .contenu-editeur').addEventListener('focus', function () {
				document.querySelector('#texte').classList.add('focus')
			})
			document.querySelector('#texte .contenu-editeur').addEventListener('blur', function () {
				document.querySelector('#texte').classList.remove('focus')
			})
			document.querySelector('#couleur-texte').addEventListener('change', this.modifierCouleurTexte)
		},
		modifierType (type) {
			this.type =  type
			switch (type) {
			case 'section':
				this.date = ''
				this.debut = ''
				this.fin = ''
				this.lieu = ''
				this.ressource = '-'
				this.lien = ''
				this.fichier = ''
				this.depot = 'non'
				this.criteres = 'non'
				this.listeCriteres = []
				this.verrouillage = 'non'
				this.code = ''
				this.indice = ''
				this.heures = 0
				this.minutes = 0
				this.vignette = ''
				this.code = ''
				this.indice = ''
				break
			case 'seance':
				this.ressource = '-'
				this.lien = ''
				this.fichier = ''
				this.depot = 'non'
				this.criteres = 'non'
				this.listeCriteres = []
				this.vignette = 'icone_meeting_room'
				break
			case 'document':
				this.date = ''
				this.debut = ''
				this.fin = ''
				this.lieu = ''
				this.ressource = 'lien'
				this.depot = 'non'
				this.criteres = 'non'
				this.listeCriteres = []
				this.vignette = 'icone_article'
				break
			case 'exercice':
				this.date = ''
				this.debut = ''
				this.fin = ''
				this.lieu = ''
				this.ressource = 'lien'
				this.depot = 'non'
				this.criteres = 'non'
				this.listeCriteres = []
				this.vignette = 'icone_check_circle'
				break
			case 'activite':
				this.date = ''
				this.debut = ''
				this.fin = ''
				this.lieu = ''
				this.vignette = 'icone_lightbulb'
				break
			}
		},
		modifierRessource (ressource) {
			this.ressource = ressource
			this.lien = ''
			this.fichier = ''
		},
		modifierCriteres (criteres) {
			this.criteres = criteres
			if (criteres === 'non') {
				this.listeCriteres = []
			} else {
				this.listeCriteres = [{ libelle: '', etoiles: 1, evaluation: '' }]
			}
		},
		modifierVerrouillage (verrouillage) {
			this.verrouillage = verrouillage
			if (verrouillage === 'non') {
				this.code = ''
				this.indice = ''
			}
		},
		modifierVignette (vignette) {
			this.vignette = vignette
		},
		modifierCouleurTexte (event) {
			pell.exec('foreColor', event.target.value)
		},
		modifierCouleurRetroaction (event) {
			pell.exec('foreColor', event.target.value)
		},
		selectionnerFichier (event) {
			this.fichier = event.target.files[0].name
		},
		selectionnerCouleur (couleur) {
			this.couleur = couleur
		},
		ajouterCritere () {
			this.listeCriteres.push({ libelle: '', etoiles: 1, evaluation: '' })
		},
		supprimerCritere () {
			if (this.listeCriteres.length > 1) {
				this.listeCriteres.splice(this.listeCriteres.length - 1, 1)
			}
		},
		verifierBloc () {
			if (this.titre === '') {
				this.$parent.$parent.message = this.$t('donnerTitreEtape')
				return false
			} else if (this.type === '-') {
				this.$parent.$parent.message = this.$t('choisirTypeEtape')
				return false
			} else if (this.type === 'seance' && (this.date === '' || this.debut === '' || this.fin === '' || this.lieu === '')) {
				if (this.date === '') {
					this.$parent.$parent.message = this.$t('indiquerDate')
				} else if (this.debut === '') {
					this.$parent.$parent.message = this.$t('indiquerHoraireDebut')
				} else if (this.fin === '') {
					this.$parent.$parent.message = this.$t('indiquerHoraireFin')
				} else if (this.lieu === '') {
					this.$parent.$parent.message = this.$t('indiquerAdresse')
				}
				return false
			} else if ((this.type === 'document' || this.type === 'exercice' || this.type === 'activite') && (this.ressource === 'lien' && this.lien !== '' && this.verifierLien(this.lien) === false)) {
				this.$parent.$parent.message = this.$t('indiquerLienValide')
				return false
			} else if ((this.type === 'document' || this.type === 'exercice' || this.type === 'activite') && (this.ressource !== '-' && this.lien === '' && this.fichier === '')) {
				if (this.ressource === 'lien' && this.lien === '') {
					this.$parent.$parent.message = this.$t('indiquerLien')
				} else if (this.ressource === 'fichier' && this.fichier === '') {
					this.$parent.$parent.message = this.$t('indiquerFichier')
				}
				return false
			} else if (this.type !== 'section' && (this.verrouillage === 'oui' && this.code === '')) {
				this.$parent.$parent.message = this.$t('indiquerCode')
				return false
			} else if (this.type === 'activite' && this.criteres === 'oui') {
				let criteresCorrects = 0
				this.listeCriteres.forEach(function (critere) {
					if (critere.libelle !== '' && critere.etoiles !== '') {
						criteresCorrects++
					}
				})
				if (criteresCorrects < this.listeCriteres.length && this.listeCriteres.length === 1) {
					this.$parent.$parent.message = this.$t('indiquerLibelleCritere')
					return false
				} else if (criteresCorrects < this.listeCriteres.length && this.listeCriteres.length > 1) {
					this.$parent.$parent.message = this.$t('indiquerLibelleChaqueCritere')
					return false
				}
			}
			return true
		},
		televerserFichier () {
			return new Promise(function (resolve) {
				this.modale = 'televerser'
				const blob = document.querySelector('#televerser').files[0]
				if (blob.size < 2 * 1024000) {
					const formData = new FormData()
					formData.append('ancienfichier', this.ancienFichier)
					formData.append('fichier', this.fichier)
					formData.append('parcours', this.id)
					formData.append('blob', blob)
					const xhr = new XMLHttpRequest()
					xhr.onload = function () {
						if (xhr.readyState === xhr.DONE && xhr.status === 200) {
							this.progression = 0
							if (xhr.responseText === 'erreur') {
								this.$parent.$parent.message = this.$t('erreurTeleversement')
								resolve('erreur')
							} else {
								this.modale = ''
								this.ancienFichier = ''
								resolve(xhr.responseText)
							}
						} else {
							this.$parent.$parent.message = this.$t('erreurTeleversement')
							resolve('erreur')
						}
					}.bind(this)
					xhr.upload.onprogress = function (e) {
						if (e.lengthComputable) {
							const pourcentage = (e.loaded / e.total) * 100
							this.progression = Math.round(pourcentage)
						}
					}.bind(this)
					xhr.open('POST', this.$parent.$parent.hote + 'inc/televerser_fichier.php', true)
					xhr.send(formData)
				} else {
					this.$parent.$parent.message = this.$t('tailleMax', { taille: 2 })
					resolve('erreur')
				}
			}.bind(this))
		},
		remplacerImage (event, fichier) {
			event.target.src = this.definirRacine() + 'fichiers/' + this.id + '/' + fichier
		},
		async ajouterBloc () {
			if (this.verifierBloc() === false) {
				return false
			}
			if (this.ressource === 'fichier' && this.fichier !== '') {
				const fichier = await this.televerserFichier()
				if (fichier === 'erreur') {
					this.modale = 'bloc'
					this.$nextTick(function () {
						this.chargerEditeur()
					}.bind(this))
					return false
				} else {
					this.fichier = fichier
				}
			}
			this.$parent.$parent.chargement = true
			const id = 'etape-' + (new Date()).getTime() + Math.random().toString(16).slice(10)
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			const bloc = { id: id, titre: this.titre, texte: this.texte, type: this.type, date: this.date, debut: this.debut, fin: this.fin, lieu: this.lieu, lien: this.lien, fichier: this.fichier, depot: this.depot, travaux: this.travaux, criteres: this.criteres, listeCriteres: this.listeCriteres, verrouillage: this.verrouillage, code: this.code, indice: this.indice, vignette: this.vignette, heures: this.heures, minutes: this.minutes, couleur: this.couleur, visibilite: true }
			blocs.push(bloc)
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					this.fermerModaleContenu()
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = this.$t('erreurServeur')
					} else if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = this.$t('actionNonAutorisee')
					} else if (xhr.responseText === 'parcours_modifie') {
						this.blocs.push(bloc)
						this.$parent.$parent.notification = this.$t('etapeAjoutee')
						this.$nextTick(function () {
							this.verifierTextes()
						})
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleContenu()
					this.$parent.$parent.message = this.$t('erreurServeur')
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + 'inc/modifier_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }) }
			xhr.send(JSON.stringify(json))
		},
		async modifierBloc () {
			if (this.verifierBloc() === false) {
				return false
			}
			if (this.ressource === 'fichier' && this.fichier !== this.ancienFichier) {
				const fichier = await this.televerserFichier()
				if (fichier === 'erreur') {
					this.modale = 'bloc'
					this.$nextTick(function () {
						this.chargerEditeur()
					}.bind(this))
					return false
				} else {
					this.fichier = fichier
				}
			}
			const fichiers = []
			if (this.lien !== '' && this.ancienFichier !== '') {
				fichiers.push(this.ancienFichier)
			}
			this.$parent.$parent.chargement = true
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			blocs.forEach(function (bloc, index) {
				if (bloc.id === this.blocId) {
					blocs.splice(index, 1, { id: bloc.id, titre: this.titre, texte: this.texte, type: this.type, date: this.date, debut: this.debut, fin: this.fin, lieu: this.lieu, lien: this.lien, fichier: this.fichier, depot: this.depot, travaux: this.travaux, criteres: this.criteres, listeCriteres: this.listeCriteres, verrouillage: this.verrouillage, code: this.code, indice: this.indice, vignette: this.vignette, heures: this.heures, minutes: this.minutes, couleur: this.couleur, visibilite: bloc.visibilite })
				}
			}.bind(this))
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					this.fermerModaleContenu()
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = this.$t('erreurServeur')
					} else if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = this.$t('actionNonAutorisee')
					} else if (xhr.responseText === 'parcours_modifie') {
						this.blocs = blocs
						this.$parent.$parent.notification = this.$t('etapeModifiee')
						this.$nextTick(function () {
							this.verifierTextes()
						})
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleContenu()
					this.$parent.$parent.message = this.$t('erreurServeur')
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + 'inc/modifier_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }), fichiers: JSON.stringify(fichiers) }
			xhr.send(JSON.stringify(json))
		},
		fermerModaleContenu () {
			this.modale = ''
			this.mode = 'creation'
			this.titre = ''
			this.texte = ''
			this.type = '-'
			this.date = ''
			this.debut = ''
			this.fin = ''
			this.lieu = ''
			this.ressource = '-'
			this.lien = ''
			this.fichier = ''
			this.ancienFichier = ''
			this.depot = 'non'
			this.travaux = []
			this.criteres = 'non'
			this.listeCriteres = []
			this.vignette = ''
			this.verrouillage = 'non'
			this.code = ''
			this.indice = ''
			this.heures = 0
			this.minutes = 0
			this.couleur = '#00ced1'
			this.progression = 0
		},
		modifierPositionBloc () {
			this.$nextTick(function () {
				this.verifierTextes()
			})
			if (this.blocs.length > 1) {
				this.$parent.$parent.chargementBlocs = true
				const xhr = new XMLHttpRequest()
				xhr.onload = function () {
					if (xhr.readyState === xhr.DONE && xhr.status === 200) {
						this.$parent.$parent.chargementBlocs = false
						if (xhr.responseText === 'non_autorise') {
							this.$parent.$parent.message = this.$t('actionNonAutorisee')
						}
					} else {
						this.$parent.$parent.chargementBlocs = false
						this.$parent.$parent.message = this.$t('erreurServeur')
					}
				}.bind(this)
				xhr.open('POST', this.$parent.$parent.hote + 'inc/modifier_parcours.php', true)
				xhr.setRequestHeader('Content-type', 'application/json')
				const json = { parcours: this.id, donnees: JSON.stringify({ blocs: this.blocs }) }
				xhr.send(JSON.stringify(json))
			}
		},
		modifierVisibiliteBloc (id) {
			this.$parent.$parent.chargement = true
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			blocs.forEach(function (item, index) {
				if (item.id === id) {
					blocs[index].visibilite = !blocs[index].visibilite
				}
			})
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = this.$t('erreurServeur')
					} else if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = this.$t('actionNonAutorisee')
					} else if (xhr.responseText === 'parcours_modifie') {
						this.blocs = blocs
						this.$parent.$parent.notification = this.$t('visibiliteModifiee')
					}
				} else {
					this.$parent.$parent.chargement = false
					this.$parent.$parent.message = this.$t('erreurServeur')
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + 'inc/modifier_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }) }
			xhr.send(JSON.stringify(json))
		},
		dupliquerFichier (fichier) {
			return new Promise(function (resolve) {
				const xhr = new XMLHttpRequest()
				xhr.onload = function () {
					if (xhr.readyState === xhr.DONE && xhr.status === 200) {
						if (xhr.responseText === 'erreur') {
							resolve('erreur')
						} else {
							resolve(xhr.responseText)
						}
					} else {
						resolve('erreur')
					}
				}
				xhr.open('POST', this.$parent.$parent.hote + 'inc/dupliquer_fichier.php', true)
				xhr.setRequestHeader('Content-type', 'application/json')
				const json = { parcours: this.id, fichier: fichier }
				xhr.send(JSON.stringify(json))
			}.bind(this))
		},
		async dupliquerBloc (id) {
			this.$parent.$parent.chargement = true
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			let bloc = {}
			blocs.forEach(function (item) {
				if (item.id === id) {
					bloc = item
				}
			})
			bloc.id = 'etape-' + (new Date()).getTime() + Math.random().toString(16).slice(10)
			if (bloc.hasOwnProperty('fichier') === true && bloc.fichier !== '') {
				const fichier = await this.dupliquerFichier(bloc.fichier)
				if (fichier === 'erreur') {
					this.$parent.$parent.chargement = false
					return false
				} else {
					bloc.fichier = fichier
				}
			}
			if (bloc.hasOwnProperty('travaux') === true) {
				bloc.travaux = []
			}
			blocs.push(bloc)
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					this.fermerModaleContenu()
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = this.$t('erreurServeur')
					} else if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = this.$t('actionNonAutorisee')
					} else if (xhr.responseText === 'parcours_modifie') {
						this.blocs.push(bloc)
						this.$parent.$parent.notification = this.$t('etapeDupliquee')
						this.$nextTick(function () {
							this.verifierTextes()
						})
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleContenu()
					this.$parent.$parent.message = this.$t('erreurServeur')
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + 'inc/modifier_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }) }
			xhr.send(JSON.stringify(json))
		},
		afficherSupprimerBloc (id) {
			this.blocId = id
			this.modale = 'supprimer-bloc'
		},
		supprimerBloc () {
			this.modale = ''
			this.$parent.$parent.chargement = true
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			const fichiers = []
			blocs.forEach(function (item, index) {
				if (item.id === this.blocId) {
					if (item.fichier !== '') {
						fichiers.push(item.fichier)
					}
					if (item.hasOwnProperty('travaux')) {
						item.travaux.forEach(function (travail) {
							if (travail.fichier !== '') {
								fichiers.push(travail.fichier)
							}
						})
					}
					blocs.splice(index, 1)
				}
			}.bind(this))
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = this.$t('erreurServeur')
					} else if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = this.$t('actionNonAutorisee')
					} else if (xhr.responseText === 'parcours_modifie') {
						this.blocs = blocs
						this.$parent.$parent.notification = this.$t('etapeSupprimee')
					}
				} else {
					this.$parent.$parent.chargement = false
					this.$parent.$parent.message = this.$t('erreurServeur')
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + 'inc/modifier_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }), fichiers: JSON.stringify(fichiers) }
			xhr.send(JSON.stringify(json))
		},
		ouvrirModaleCriteres (criteres) {
			this.listeCriteres = criteres
			this.modale = 'criteres'
		},
		fermerModaleCriteres () {
			this.modale = ''
			this.listeCriteres = []
		},
		ouvrirModaleTravaux (bloc) {
			this.blocId = bloc.id
			if (bloc.hasOwnProperty('travaux') ===  true) {
				this.travaux = bloc.travaux
			}
			if (bloc.hasOwnProperty('listeCriteres') ===  true) {
				this.listeCriteres = bloc.listeCriteres
			}
			this.modale = 'travaux'
		},
		afficherRetroaction (travail) {
			if (parseInt(travail.motdepasse) === parseInt(this.motdepasseRetroaction)) {
				this.motdepasseRetroaction = ''
			} else {
				this.evaluation = []
				this.motdepasseRetroaction = travail.motdepasse
				this.retroaction = travail.retroaction
				if (travail.hasOwnProperty('evaluation') === true && this.listeCriteres.length > 0) {
					this.listeCriteres.forEach(function (critere, index) {
						if (!travail.evaluation[index]) {
							travail.evaluation[index] = 0
						} else if (parseInt(travail.evaluation[index]) > critere.etoiles) {
							travail.evaluation[index] = critere.etoiles
						}
					})
					this.evaluation = travail.evaluation
				} else if (!travail.hasOwnProperty('evaluation') && this.listeCriteres.length > 0) {
					this.listeCriteres.forEach(function () {
						this.evaluation.push(0)
					}.bind(this))
				}
				this.$nextTick(function () {
					document.querySelector('#retroaction').innerHTML = ''
					const that = this
					const editeur = pell.init({
						element: document.querySelector('#retroaction'),
						onChange: function (html) {
							let texte = html.replace(/(<a [^>]*)(target="[^"]*")([^>]*>)/gi, '$1$3')
							texte = texte.replace(/(<a [^>]*)(>)/gi, '$1 target="_blank"$2')
							texte = linkifyHtml(texte, {
								defaultProtocol: 'https'
							})
							this.retroaction = texte
						}.bind(this),
						actions: [
							{ name: 'gras', title: that.$t('gras'), icon: '<i class="material-icons">format_bold</i>', result: () => pell.exec('bold') },
							{ name: 'italique', title: that.$t('italique'), icon: '<i class="material-icons">format_italic</i>', result: () => pell.exec('italic') },
							{ name: 'souligne', title: that.$t('souligne'), icon: '<i class="material-icons">format_underlined</i>', result: () => pell.exec('underline') },
							{ name: 'barre', title: that.$t('barre'), icon: '<i class="material-icons">format_strikethrough</i>', result: () => pell.exec('strikethrough') },
							{ name: 'listeordonnee', title: that.$t('listeOrdonnee'), icon: '<i class="material-icons">format_list_numbered</i>', result: () => pell.exec('insertOrderedList') },
							{ name: 'liste', title: that.$t('liste'), icon: '<i class="material-icons">format_list_bulleted</i>', result: () => pell.exec('insertUnorderedList') },
							{ name: 'couleur', title: that.$t('couleurTexte'), icon: '<label for="couleur-retroaction"><i class="material-icons">format_color_text</i></label><input id="couleur-retroaction" type="color">', result: () => undefined },
							{ name: 'lien', title: that.$t('lien'), icon: '<i class="material-icons">link</i>', result: () => { const url = window.prompt(that.$t('adresseLien')); if (url) { pell.exec('createLink', url) } } }
						],
						classes: { actionbar: 'boutons-editeur', button: 'bouton-editeur', content: 'contenu-editeur', selected: 'bouton-actif' }
					})
					editeur.content.innerHTML = this.retroaction
					editeur.onpaste = function (event) {
						event.preventDefault()
						event.stopPropagation()
						const texte = event.clipboardData.getData('text/plain')
						pell.exec('insertText', texte)
					}
					document.querySelector('#retroaction .contenu-editeur').addEventListener('focus', function () {
						document.querySelector('#retroaction').classList.add('focus')
					})
					document.querySelector('#retroaction .contenu-editeur').addEventListener('blur', function () {
						document.querySelector('#retroaction').classList.remove('focus')
					})
					document.querySelector('#couleur-retroaction').addEventListener('change', this.modifierCouleurRetroaction)
				}.bind(this))
			}
		},
		enregistrerRetroaction () {
			if (this.retroaction === '') {
				this.$parent.$parent.message = this.$t('indiquerCommentaire')
				return false
			}
			if (this.listeCriteres.length > 0) {
				let criteresEvalues = 0
				this.evaluation.forEach(function (item) {
					if (item !== 0) {
						criteresEvalues++
					}
				})
				if (criteresEvalues !== this.listeCriteres.length) {
					this.$parent.$parent.message = this.$t('remplirCriteres')
					return false
				}
			}
			this.$parent.$parent.chargementBlocs = true
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			blocs.forEach(function (bloc) {
				if (bloc.id === this.blocId) {
					bloc.travaux.forEach(function (travail) {
						if (parseInt(travail.motdepasse) === parseInt(this.motdepasseRetroaction)) {
							travail.retroaction = this.retroaction
							travail.evaluation = this.evaluation
						}
					}.bind(this))
				}
			}.bind(this))
			const travaux = JSON.parse(JSON.stringify(this.travaux))
			travaux.forEach(function (travail) {
				if (parseInt(travail.motdepasse) === parseInt(this.motdepasseRetroaction)) {
					travail.retroaction = this.retroaction
					travail.evaluation = this.evaluation
				}
			}.bind(this))
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargementBlocs = false
					if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = this.$t('actionNonAutorisee')
					} else if (xhr.responseText === 'parcours_modifie') {
						this.blocs = blocs
						this.travaux = travaux
						this.motdepasseRetroaction = ''
						this.$parent.$parent.notification = this.$t('evaluationEnregistree')
					}
				} else {
					this.$parent.$parent.chargementBlocs = false
					this.$parent.$parent.message = this.$t('erreurServeur')
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + 'inc/modifier_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }) }
			xhr.send(JSON.stringify(json))
		},
		annulerRetroaction () {
			this.motdepasseRetroaction = ''
		},
		afficherSupprimerTravail (motdepasse) {
			this.confirmation = 'supprimer-travail'
			this.motdepasseTravail = motdepasse
		},
		annulerSupprimerTravail () {
			this.confirmation = ''
			this.motdepasseTravail = ''
		},
		supprimerTravail () {
			this.confirmation = ''
			this.$parent.$parent.chargementBlocs = true
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			let travaux = []
			const fichiers = []
			blocs.forEach(function (item, indexBloc) {
				if (item.id === this.blocId) {
					item.travaux.forEach(function (travail, indexTravail) {
						if (parseInt(travail.motdepasse) === parseInt(this.motdepasseTravail)) {
							if (travail.fichier !== '') {
								fichiers.push(travail.fichier)
							}
							blocs[indexBloc].travaux.splice(indexTravail, 1)
							travaux = blocs[indexBloc].travaux
						}
					}.bind(this))
				}
			}.bind(this))
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargementBlocs = false
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = this.$t('erreurServeur')
					} else if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = this.$t('actionNonAutorisee')
					} else if (xhr.responseText === 'parcours_modifie') {
						this.blocs = blocs
						this.travaux = travaux
						if (this.travaux.length === 0) {
							this.fermerModaleTravaux()
						}
						if (parseInt(this.motdepasseTravail) === parseInt(this.motdepasseRetroaction)) {
							this.motdepasseRetroaction = ''
						}
						this.motdepasseTravail = ''
						this.$parent.$parent.notification = this.$t('travailSupprime')
					}
				} else {
					this.$parent.$parent.chargementBlocs = false
					this.$parent.$parent.message = this.$t('erreurServeur')
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + 'inc/modifier_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }), fichiers: JSON.stringify(fichiers) }
			xhr.send(JSON.stringify(json))
		},
		fermerModaleTravaux () {
			this.modale = ''
			this.blocId = ''
			this.travaux = []
			this.listeCriteres = []
			this.retroaction = ''
			this.evaluation = []
			this.motdepasseRetroaction = ''
			this.motdepasseTravail = ''
		},
		debloquerEtape (id) {
			const code = document.querySelector('#' + id + ' input[type="text"]').value
			let etapeDebloquee = false
			this.blocs.forEach(function (bloc) {
				if (bloc.id === id && code === bloc.code) {
					this.blocId = id
					this.chargement = true
					bloc.code = ''
					etapeDebloquee = true
					this.$nextTick(function () {
						this.verifierTextes()
					}.bind(this))
					setTimeout(function () {
						this.chargement = false
						this.blocId = ''
						this.$parent.$parent.notification = this.$t('etapeDebloquee')
					}.bind(this), 300)
				}
			}.bind(this))
			if (etapeDebloquee === false) {
				this.$parent.$parent.message = this.$t('codeIncorrect')
			}
		},
		ouvrirModaleDepot (id) {
			this.blocId = id
			this.mode = '-'
			this.modale = 'depot'
		},
		modifierModeDepot (mode) {
			this.mode = mode
			if (mode === 'deposer') {
				this.ressource = 'lien'
				this.motdepasse = Math.floor(100000 + Math.random() * 900000)
			} else {
				this.ressource = '-'
				this.motdepasse = ''
			}
		},
		modifierRessourceDepot (ressource) {
			this.ressource = ressource
			this.lien = ''
			this.fichier = ''
		},
		async deposer () {
			if (this.pseudo === '') {
				this.$parent.$parent.message = this.$t('indiquerNom')
				return false
			} else if (this.ressource === 'lien' && this.lien !== '' && this.verifierLien(this.lien) === false) {
				this.$parent.$parent.message = this.$t('indiquerLienValide')
				return false
			} else if (this.lien === '' && this.fichier === '') {
				if (this.ressource === 'lien' && this.lien === '') {
					this.$parent.$parent.message = this.$t('indiquerLien')
				} else if (this.ressource === 'fichier' && this.fichier === '') {
					this.$parent.$parent.message = this.$t('indiquerFichier')
				}
				return false
			}
			if (this.ressource === 'fichier' && this.fichier !== '') {
				this.fichier = await this.televerserFichier()
				if (this.fichier === 'erreur') {
					this.fermerModaleDepot()
					return false
				}
			}
			let fichierasupprimer = ''
			if (this.lien !== '' && this.ancienFichier !== '') {
				fichierasupprimer = this.ancienFichier
			}
			const date = moment().format()
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			if (this.mode === 'deposer') {
				blocs.forEach(function (bloc) {
					if (bloc.id === this.blocId) {
						bloc.travaux.push({ motdepasse: this.motdepasse, pseudo: this.pseudo, lien: this.lien, fichier: this.fichier, retroaction: '', date: date })
					}
				}.bind(this))
			} else if (this.mode === 'afficher') {
				blocs.forEach(function (bloc) {
					if (bloc.id === this.blocId) {
						bloc.travaux.forEach(function (travail) {
							if (parseInt(travail.motdepasse) === parseInt(this.motdepasse)) {
								travail.pseudo = this.pseudo
								travail.lien = this.lien
								travail.fichier = this.fichier
								travail.date = date
							}
						}.bind(this))
					}
				}.bind(this))
			}
			this.$parent.$parent.chargement = true
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					this.fermerModaleDepot()
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = this.$t('erreurServeur')
					} else if (xhr.responseText === 'travail_depose') {
						this.blocs = blocs
						this.$parent.$parent.notification = this.$t('travailDepose')
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleDepot()
					this.$parent.$parent.message = this.$t('erreurServeur')
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + 'inc/deposer_travail.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, id: this.blocId, mode: this.mode, motdepasse: this.motdepasse, pseudo: this.pseudo, lien: this.lien, fichier: this.fichier, date: date, fichierasupprimer: fichierasupprimer }
			xhr.send(JSON.stringify(json))
		},
		verifier () {
			if (this.motdepasse === '') {
				this.$parent.$parent.message = this.$t('indiquerMotDePasse')
				return false
			}
			this.blocs.forEach(function (item) {
				if (item.id === this.blocId) {
					const travaux = item.travaux
					let motdepasseExiste = false
					travaux.forEach(function (travail) {
						if (parseInt(travail.motdepasse) === parseInt(this.motdepasse)) {
							this.mode = 'afficher'
							this.pseudo = travail.pseudo
							this.lien = travail.lien
							this.fichier = travail.fichier
							this.ancienFichier = travail.fichier
							if (this.lien !== '') {
								this.ressource = 'lien'
							} else if (this.fichier !== '') {
								this.ressource = 'fichier'
							}
							this.retroaction = travail.retroaction
							if (item.hasOwnProperty('listeCriteres')) {
								this.listeCriteres = item.listeCriteres
							}
							if (travail.hasOwnProperty('evaluation')) {
								this.evaluation = travail.evaluation
							}
							motdepasseExiste = true
						}
					}.bind(this))
					if (motdepasseExiste === false) {
						this.$parent.$parent.message = this.$t('motDePasseExistePas')
					}
				}
			}.bind(this))
		},
		afficherSupprimerDepot () {
			this.confirmation = 'supprimer-depot'
		},
		supprimerDepot () {
			this.confirmation = ''
			let fichierasupprimer = ''
			if (this.fichier !== '') {
				fichierasupprimer = this.fichier
			}
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			blocs.forEach(function (bloc, indexBloc) {
				if (bloc.id === this.blocId) {
					bloc.travaux.forEach(function (travail, indexTravail) {
						if (parseInt(travail.motdepasse) === parseInt(this.motdepasse)) {
							blocs[indexBloc].travaux.splice(indexTravail, 1)
						}
					}.bind(this))
				}
			}.bind(this))
			this.$parent.$parent.chargementBlocs = true
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargementBlocs = false
					this.fermerModaleDepot()
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = this.$t('erreurServeur')
					} else if (xhr.responseText === 'travail_supprime') {
						this.blocs = blocs
						this.$parent.$parent.notification = this.$t('travailSupprime')
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleDepot()
					this.$parent.$parent.message = this.$t('erreurServeur')
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + 'inc/supprimer_travail.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, id: this.blocId, motdepasse: this.motdepasse, fichierasupprimer: fichierasupprimer }
			xhr.send(JSON.stringify(json))
		},
		fermerModaleDepot () {
			this.blocId = ''
			this.pseudo = ''
			this.motdepasse = ''
			this.lien = ''
			this.fichier = ''
			this.ancienFichier = ''
			this.mode = ''
			this.ressource = '-'
			this.retroaction = ''
			this.listeCriteres = []
			this.evaluation = []
			this.modale = ''
		},
		ouvrirModaleParcours () {
			this.modale = 'parcours'
		},
		fermerModaleParcours () {
			this.modale = ''
			this.question = ''
			this.reponse = ''
			this.nouveaunom = ''
			this.nouvellequestion = ''
			this.nouvellereponse = ''
		},
		ouvrirModaleNomParcours () {
			this.nouveaunom = this.nom
			this.modale = 'modifier-nom'
		},
		fermerModaleNomParcours () {
			this.modale = ''
			this.nouveaunom = ''
		},
		modifierNomParcours () {
			if (this.nouveaunom !== '' && this.nom !== this.nouveaunom) {
				this.$parent.$parent.chargement = true
				const nom = this.nouveaunom
				const xhr = new XMLHttpRequest()
				xhr.onload = function () {
					if (xhr.readyState === xhr.DONE && xhr.status === 200) {
						this.$parent.$parent.chargement = false
						this.fermerModaleNomParcours()
						if (xhr.responseText === 'erreur') {
							this.$parent.$parent.message = this.$t('erreurServeur')
						} else if (xhr.responseText === 'non_autorise') {
							this.$parent.$parent.message = this.$t('actionNonAutorisee')
						} else if (xhr.responseText === 'nom_modifie') {
							this.nom = nom
							this.$parent.$parent.notification = this.$t('nomModifie')
							document.title = this.nom + ' - Digisteps by La Digitale'
						}
					} else {
						this.$parent.$parent.chargement = false
						this.fermerModaleNomParcours()
						this.$parent.$parent.message = this.$t('erreurServeur')
					}
				}.bind(this)
				xhr.open('POST', this.$parent.$parent.hote + 'inc/modifier_nom_parcours.php', true)
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
				xhr.send('parcours=' + this.id + '&nouveaunom=' + nom)
			} else if (this.nouveaunom === '') {
				this.$parent.$parent.message = this.$t('remplirChampNouveauNom')
			}
		},
		ouvrirModaleAccesParcours () {
			this.modale = 'modifier-acces'
		},
		fermerModaleAccesParcours () {
			this.modale = ''
			this.question = ''
			this.reponse = ''
			this.nouvellequestion = ''
			this.nouvellereponse = ''
		},
		modifierAccesParcours () {
			if (this.question !== '' && this.reponse !== '' && this.nouvellequestion !== '' && this.nouvellereponse !== '') {
				this.$parent.$parent.chargement = true
				const xhr = new XMLHttpRequest()
				xhr.onload = function () {
					if (xhr.readyState === xhr.DONE && xhr.status === 200) {
						this.$parent.$parent.chargement = false
						this.fermerModaleAccesParcours()
						if (xhr.responseText === 'erreur') {
							this.$parent.$parent.message = this.$t('erreurServeur')
						} else if (xhr.responseText === 'non_autorise') {
							this.$parent.$parent.message = this.$t('informationsIncorrectes')
						} else if (xhr.responseText === 'acces_modifie') {
							this.$parent.$parent.notification = this.$t('accesModifie')
						}
					} else {
						this.$parent.$parent.chargement = false
						this.fermerModaleAccesParcours()
						this.$parent.$parent.message = this.$t('erreurServeur')
					}
				}.bind(this)
				xhr.open('POST', this.$parent.$parent.hote + 'inc/modifier_acces_parcours.php', true)
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
				xhr.send('parcours=' + this.id + '&question=' + this.question + '&reponse=' + this.reponse + '&nouvellequestion=' + this.nouvellequestion + '&nouvellereponse=' + this.nouvellereponse)
			} else if (this.question === '') {
				this.$parent.$parent.message = this.$t('selectionnerQuestionSecreteActuelle')
			} else if (this.reponse === '') {
				this.$parent.$parent.message = this.$t('indiquerReponseSecreteActuelle')
			} else if (this.nouvellequestion === '') {
				this.$parent.$parent.message = this.$t('selectionnerNouvelleQuestionSecrete')
			} else if (this.nouvellereponse === '') {
				this.$parent.$parent.message = this.$t('indiquerNouvelleReponseSecrete')
			}
		},
		ouvrirModaleConnexion () {
			this.modale = 'connexion'
		},
		fermerModaleConnexion () {
			this.modale = ''
			this.question = ''
			this.reponse = ''
		},
		debloquerParcours () {
			if (this.question !== '' && this.reponse !== '') {
				this.$parent.$parent.chargement = true
				const xhr = new XMLHttpRequest()
				xhr.onload = function () {
					if (xhr.readyState === xhr.DONE && xhr.status === 200) {
						this.$parent.$parent.chargement = false
						this.fermerModaleConnexion()
						if (xhr.responseText === 'erreur') {
							this.$parent.$parent.message = this.$t('erreurServeur')
						} else if (xhr.responseText === 'non_autorise') {
							this.$parent.$parent.message = this.$t('informationsIncorrectes')
						} else if (xhr.responseText === 'parcours_debloque') {
							this.admin = true
							this.$parent.$parent.notification = this.$t('parcoursDebloque')
							this.$nextTick(function () {
								this.verifierTextes()
							})
						}
					} else {
						this.$parent.$parent.chargement = false
						this.fermerModaleConnexion()
						this.$parent.$parent.message = this.$t('erreurServeur')
					}
				}.bind(this)
				xhr.open('POST', this.$parent.$parent.hote + 'inc/ouvrir_parcours.php', true)
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
				xhr.send('parcours=' + this.id + '&question=' + this.question + '&reponse=' + this.reponse)
			} else if (this.question === '') {
				this.$parent.$parent.message = this.$t('selectionnerQuestionSecrete')
			} else if (this.reponse === '') {
				this.$parent.$parent.message = this.$t('remplirReponseSecrete')
			}
		},
		exporterParcours () {
			this.modale = ''
			this.$parent.$parent.chargement = true
			const donnees = { blocs: this.blocs }
			let nom = latinise(this.nom.toLowerCase())
			nom = nom.replace(/ /gi, '-')
			nom = nom.replace(/[^0-9a-z_-]/gi, '')
			if (nom.length > 100) {
				nom = nom.substring(0, 100)
			}
			const zip = new JSZip()
			const fichiers = []
			this.blocs.forEach(function (bloc) {
				if (bloc.hasOwnProperty('travaux')) {
					bloc.travaux = []
				}
				if (bloc.hasOwnProperty('fichier') && bloc.fichier !== '') {
					fichiers.push(bloc.fichier)
				}
			})
			const donneesFichiers = []
			for (const fichier of fichiers) {
				const donneesFichier = new Promise(function (resolve) {
					const xhr = new XMLHttpRequest()
					xhr.onload = function () {
						if (xhr.readyState === xhr.DONE && xhr.status === 200) {
							resolve({ nom: fichier, binaire: this.response })
						} else {
							resolve({ nom: '', binaire: '' })
						}
					}
					xhr.onerror = function () {
						resolve({ nom: '', binaire: '' })
					}
					xhr.open('GET', this.definirRacine() + 'fichiers/' + this.id + '/' + fichier, true)
					xhr.responseType = 'arraybuffer'
					xhr.send()
				}.bind(this))
				donneesFichiers.push(donneesFichier)
			}
			Promise.all(donneesFichiers).then(function (resultat) {
				resultat.forEach(function (item) {
					if (item.nom !== '' && item.binaire !== '') {
						zip.folder('fichiers').file(item.nom, item.binaire, { binary: true })
					}
				})
				zip.file('donnees.json', JSON.stringify(donnees))
				zip.generateAsync({ type: 'blob' }).then(function (archive) {
					this.$parent.$parent.chargement = false
					saveAs(archive, nom + '_' + new Date().getTime() + '.zip')
					this.$parent.$parent.notification = this.$t('parcoursExporte')
				}.bind(this))
			}.bind(this))
		},
		importerParcours (event) {
			const fichier = event.target.files[0]
			if (fichier === null || fichier.length === 0) {
				document.querySelector('#importer').value = ''
				return false
			} else {
				this.modale = ''
				this.$parent.$parent.chargement = true
				const donneesFichiers = []
				const jszip = new JSZip()
				jszip.loadAsync(fichier).then(function (archive) {
					if (archive.files['donnees.json'] && archive.files['donnees.json'] !== '') {
						archive.files['donnees.json'].async('string').then(function (donnees) {
							donnees = JSON.parse(donnees)
							let indexFichier = 0
							let nombreFichiers = 0
							donnees.blocs.forEach(function (item) {
								if (item.hasOwnProperty('fichier') && item.fichier !== '') {
									nombreFichiers++
								}
							})
							if (nombreFichiers === 0) {
								new Promise(function (resolve) {
									const xhr = new XMLHttpRequest()
									xhr.onload = function () {
										resolve('dossier_vide')
									}.bind(this)
									xhr.onerror = function () {
										resolve('erreur_televersement')
									}
									xhr.open('POST', this.$parent.$parent.hote + 'inc/vider_dossier_parcours.php', true)
									xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
									xhr.send('parcours=' + this.id)
								}.bind(this))
							} else {
								for (const item of donnees.blocs) {
									if (item.hasOwnProperty('fichier') && item.fichier !== '') {
										const donneesFichier = new Promise(function (resolve) {
											archive.files['fichiers/' + item.fichier].async('blob').then(function (blob) {
												indexFichier++
												const formData = new FormData()
												formData.append('index', indexFichier)
												formData.append('fichier', item.fichier)
												formData.append('parcours', this.id)
												formData.append('blob', blob)
												const xhr = new XMLHttpRequest()
												xhr.onload = function () {
													if (xhr.readyState === xhr.DONE && xhr.status === 200) {
														resolve('fichier_televerse')
													} else {
														resolve('erreur_televersement')
													}
												}.bind(this)
												xhr.onerror = function () {
													resolve('erreur_televersement')
												}
												xhr.open('POST', this.$parent.$parent.hote + 'inc/televerser_fichier_import.php', true)
												xhr.send(formData)
											}.bind(this))
										}.bind(this))
										donneesFichiers.push(donneesFichier)
									}
								}
							}
							Promise.all(donneesFichiers).then(function () {
								const xhr = new XMLHttpRequest()
								xhr.onload = function () {
									if (xhr.readyState === xhr.DONE && xhr.status === 200) {
										this.$parent.$parent.chargement = false
										if (xhr.responseText === 'erreur') {
											this.$parent.$parent.message = this.$t('erreurServeur')
										} else if (xhr.responseText === 'non_autorise') {
											this.$parent.$parent.message = this.$t('actionNonAutorisee')
										} else if (xhr.responseText === 'parcours_modifie') {
											this.blocs = donnees.blocs
											this.$parent.$parent.notification = this.$t('parcoursImporte')
											this.$nextTick(function () {
												this.verifierTextes()
											})
										}
									} else {
										this.$parent.$parent.chargement = false
										this.$parent.$parent.message = this.$t('erreurServeur')
									}
								}.bind(this)
								xhr.open('POST', this.$parent.$parent.hote + 'inc/modifier_parcours.php', true)
								xhr.setRequestHeader('Content-type', 'application/json')
								const json = { parcours: this.id, donnees: JSON.stringify({ blocs: donnees.blocs }) }
								xhr.send(JSON.stringify(json))
							}.bind(this))
						}.bind(this))
					}
				}.bind(this))
			}
		},
		terminerSession () {
			this.$parent.$parent.chargement = true
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					if (xhr.responseText === 'session_terminee') {
						this.fermerModaleParcours()
						this.admin = false
						this.$parent.$parent.notification = this.$t('sessionTerminee')
						this.$nextTick(function () {
							this.verifierTextes()
						})
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleParcours()
					this.$parent.$parent.message = this.$t('erreurServeur')
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + 'inc/terminer_session_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
			xhr.send('parcours=' + this.id)
		},
		afficherSupprimerParcours () {
			this.confirmation = 'supprimer-parcours'
		},
		supprimerParcours () {
			this.confirmation = ''
			this.$parent.$parent.chargement = true
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = this.$t('erreurServeur')
					} else if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = this.$t('actionNonAutorisee')
					} else if (xhr.responseText === 'parcours_supprime') {
						document.title = 'Digisteps by La Digitale'
						this.$router.push('/')
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleConnexion()
					this.$parent.$parent.message = this.$t('erreurServeur')
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + 'inc/supprimer_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
			xhr.send('parcours=' + this.id)
		},
		eclaircirCouleur (hex) {
			const r = parseInt(hex.slice(1, 3), 16)
			const v = parseInt(hex.slice(3, 5), 16)
			const b = parseInt(hex.slice(5, 7), 16)
			return 'rgba(' + r + ', ' + v + ', ' + b + ', ' + 0.25 + ')'
		},
		modifierCouleur (couleur, pourcentage) {
			let R = parseInt(couleur.substring(1, 3), 16)
			let G = parseInt(couleur.substring(3, 5), 16)
			let B = parseInt(couleur.substring(5, 7), 16)
			R = parseInt(R * (100 + pourcentage) / 100)
			G = parseInt(G * (100 + pourcentage) / 100)
			B = parseInt(B * (100 + pourcentage) / 100)
			R = (R < 255) ? R : 255
			G = (G < 255) ? G : 255
			B = (B < 255) ? B : 255
			const RR = ((R.toString(16).length === 1) ? '0' + R.toString(16) : R.toString(16))
			const GG = ((G.toString(16).length === 1) ? '0' + G.toString(16) : G.toString(16))
			const BB = ((B.toString(16).length === 1) ? '0' + B.toString(16) : B.toString(16))
			return '#' + RR + GG + BB
		},
		verifierLien (lien) {
			let url
			try {
				url = new URL(lien)
			} catch (_) {
				return false
			}
			return url.protocol === 'http:' || url.protocol === 'https:'
		},
		verifierImage (fichier) {
			const extensions = ['jpg', 'jpeg', 'png', 'gif']
			const extension = fichier.split('.').pop()
			if (extension !== fichier && extensions.includes(extension) === true) {
				return true
			} else {
				return false
			}
		}
	}
}
</script>

<style scoped>
#page,
#parcours {
	width: 100%;
}

#parcours header {
	position: sticky;
	top: 0;
	left: 0;
	right: 0;
	height: 40px;
    width: 100%;
    background: #fff;
    color: #001d1d;
	z-index: 100;
	user-select: none;
}

#conteneur-header {
	display: flex;
    padding: 0 20px;
    margin: auto;
    width: 100%;
	max-width: 1024px;
}

#conteneur-partage,
#conteneur-parametres,
#conteneur-logo {
    height: 40px;
	line-height: 40px;
    background: #fff;
}

#conteneur-logo {
	width: 32px;
	text-align: center;
}

#conteneur-partage,
#conteneur-parametres {
	width: 24px;
	text-align: center;
	cursor: pointer;
}

#conteneur-partage i,
#conteneur-parametres i {
	font-size: 24px;
}

#conteneur-partage {
	margin-right: 15px;
	margin-left: 8px;
}

#logo {
    width: 24px;
    height: 24px;
    background: #00ced1;
    display: inline-block;
    border-radius: 50%;
    margin: 8px 8px 8px 0;
}

#titre {
	width: calc(100% - 103px);
	font-family: 'Roboto-Slab';
	font-size: 18px;
    padding: 0 7px;
    line-height: 40px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    text-align: left;
	text-transform: uppercase;
    letter-spacing: 1px;
}

#titre:before {
    content: '';
    position: absolute;
	left: 0;
    right: 0;
    width: 100%;
    top: 100%;
    bottom: auto;
    height: 8px;
    pointer-events: none;
    background: linear-gradient(180deg, rgba(0, 0, 0, 0.25) 0, rgba(0, 0, 0, 0.08) 40%, rgba(0, 0, 0, 0.04) 50%, transparent 90%, transparent);
}

article,
section {
	position: relative;
}

#actions {
	font-size: 0;
}

#blocs.vide {
	margin: 20px auto;
	padding-top: 7em;
	padding-bottom: 7em;
	border-top: 1px solid #ddd;
	border-bottom: 1px solid #ddd;
}

#blocs.vide p {
	display: block;
	width: 100%;
    font-size: 1.1em;
    line-height: 1.2;
	margin-bottom: 0;
	text-align: center;
}

#blocs .bloc:not(.section) {
	display: flex;
	align-items: center;
	border-radius: 7px;
	padding: 15px;
	margin-top: 15px;
	width: 100%;
}

#blocs .bloc.section {
	display: flex;
	align-items: center;
	padding: 0;
	margin-top: 30px;
	width: 100%;
}

#blocs .bloc.sortable-drag {
	opacity: 1!important;
}

#blocs .bloc.sortable-chosen.sortable-ghost {
	opacity: 0.5;
	border-style: dashed;
	outline: none!important;
}

#blocs.admin .bloc {
	user-select: none;
	cursor: move;
}

#blocs:not(.admin) .bloc:first-child {
	margin-top: 0;
}

#blocs:not(.admin) .bloc.section:first-child {
	margin-top: 10px;
}

#blocs .bloc.invisible {
	opacity: 0.5;
}

#blocs .bloc:not(.section) .contenu {
	width: calc(100% - 203px);
}

#blocs .bloc.section .contenu {
	width: 100%;
}

#blocs .bloc .vignette {
	position: relative;
	padding: 7px;
	margin-right: 15px;
	border: 2px solid transparent;
	border-radius: 7px;
	background: #fff;
}

#blocs .bloc .vignette .icone {
	display: block;
	font-size: 96px;
	padding: 10px 37px;
	text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
}

#blocs .bloc .vignette .image {
	font-size: 0;
}

#blocs .bloc .vignette .image img {
	max-width: 170px;
}

#blocs .bloc .vignette .cadenas {
	position: absolute;
	font-size: 48px;
	bottom: -12px;
	left: -18px;
	line-height: 1;
}

#blocs .bloc .duree {
	position: absolute;
	top: -12px;
	left: -12px;
	padding: 5px 10px;
	border-radius: 7px;
	color: #fff;
	background: #222222;
	font-size: 12px;
}

#blocs .bloc .titre {
	font-family: 'Roboto-Slab';
	display: block;
	font-size: 18px;
	padding-bottom: 10px;
	border-bottom: 1px dashed transparent;
	line-height: 1.4;
}

#blocs .bloc .texte {
	display: block;
	font-size: 15px;
	margin-top: 12px;
	line-height: 1.5;
}

#blocs .bloc.section .titre {
	text-transform: uppercase;
	border-bottom: 3px solid #555;
	padding-bottom: 15px;
	letter-spacing: 1px;
}

#blocs .bloc.section .texte {
	margin-top: 15px;
	margin-bottom: 0;
	font-size: 16px;
}

#blocs .bloc .date-et-horaire {
	display: flex;
	font-size: 14px;
	margin-top: 8px;
	line-height: 1;
}

#blocs .bloc .texte + .action,
#blocs .bloc .texte + .date-et-horaire {
	margin-top: 12px;
}

#blocs .bloc .lien,
#blocs .bloc .lieu,
#blocs .bloc .date,
#blocs .bloc .horaire {
	display: flex;
	align-items: center;
}

#blocs .bloc .lien,
#blocs .bloc .lieu {
	display: flex;
	align-items: center;
	font-size: 14px;
	margin-top: 8px;
	line-height: 1;
}

#blocs .bloc .date {
	margin-right: 12px;
}

#blocs .bloc .lien i,
#blocs .bloc .lieu i,
#blocs .bloc .date i,
#blocs .bloc .horaire i {
	font-size: 24px;
	margin-right: 7px;
}

#blocs .bloc .titre + .action {
	margin-top: 15px;
}

#blocs .bloc .contenu .image + .action,
#blocs .bloc .contenu .image {
	margin-top: 12px;
}

#blocs .bloc .contenu .image {
	display: block;
}

#blocs .bloc .contenu .image img {
	max-height: 250px;
	border-radius: 7px;
}

#blocs .bloc.verrouille .titre {
	margin-bottom: 15px;
}

#blocs .bloc.verrouille label {
	display: block;
	width: 100%;
	font-weight: 700;
	font-size: 14px;
	margin-bottom: 10px;
	user-select: none;
}

#blocs .bloc.verrouille input[type="text"] {
	display: block;
    width: 100%;
    font-size: 16px;
    border: 2px solid transparent;
    border-radius: 4px;
	background: #fff;
    padding: 7px 15px;
    line-height: 1.5;
    margin: 0 auto 15px;
    text-align: left;
}

#blocs .bloc.verrouille input[type="text"]:focus {
	border-color: #001d1d!important;
}

#blocs .bloc .action {
	display: flex;
	justify-content: flex-end;
	align-items: center;
	margin-top: 8px;
}

#blocs .bloc .action .bouton {
	display: inline-block;
	font-weight: 700;
	font-size: 12px;
	text-transform: uppercase;
	height: 40px;
	line-height: 36px;
	padding: 0 20px;
	cursor: pointer;
	color: #001d1d;
	background: #fff;
	border: 2px solid transparent;
	border-radius: 5px;
	letter-spacing: 1px;
	text-indent: 1px;
	user-select: none;
	transition: all ease-in 0.1s;
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
}

#blocs .bloc .action .bouton.icone {
    font-size: 24px;
    font-weight: 400;
	letter-spacing: 0;
	text-indent: 0;
}

#blocs .bloc.verrouille .action .bouton.icone {
	padding: 0;
    font-size: 36px;
    border: none;
    height: auto;
    line-height: 1;
	background: transparent;
}

#blocs .bloc .action .bouton + .bouton {
	margin-left: 15px;
}

#blocs .bloc .action .bouton:hover {
	background: rgba(255, 255, 255, 0.75);
	text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
}

#blocs .bloc .action .bouton.travaux {
	color: #001d1d!important; 
	border-color: #001d1d!important;
}

#blocs .bloc .action .bouton.travaux:hover {
	background: #eee!important;
}

#blocs .bloc .actions {
	display: none;
	position: absolute;
	top: 0;
	right: 0;
	padding: 0.25em 0.5em;
	border-radius: 4px;
	background: rgba(0, 0, 0, 0.4);
	cursor: default;
}

#blocs .bloc:hover .actions {
	display: block;
}

#blocs .bloc:hover .actions span {
	font-size: 24px;
	color: #fff;
	text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
	margin-right: 0.5em;
	cursor: pointer;
}

#blocs .bloc:hover .actions span:hover {
  opacity: 0.85;
}

#blocs .bloc:hover .actions span:last-child {
	margin-right: 0;
}

#conteneur-parcours {
	padding: 20px 20px 100px 20px;
    margin: auto;
    width: 100%;
	max-width: 1024px;
}

#menu-partager {
	position: absolute;
    z-index: 1000;
    border: 1px solid #ddd;
    padding: 1rem;
    border-radius: 1rem;
	background: #fff;
	box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.25);
	top: 54px;
	width: 300px;
}
	
#menu-partager:after {
	border: solid #ddd;
	content: '';
	display: block;
	position: absolute;
	top: 0;
	border-width: 0 1px 1px 0;
	transform: translate(-7px, -8px) rotate(-135deg);
	background: #fff;
    left: 90%;
    width: 14px;
    height: 14px;
}

#menu-partager label {
	display: block;
    width: 100%;
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 10px;
    user-select: none;
}

#menu-partager .copier {
	display: flex;
	justify-content: center;
	align-items: center;
}

#menu-partager .copier input {
	display: block;
	width: calc(100% - 34px);
	font-weight: 400;
	font-size: 16px;
	border: 1px solid #ddd;
	border-radius: 4px;
	padding: 7px 15px;
	line-height: 1.5;
	text-align: left;
	margin-right: 10px;
}

#copier-iframe,
#copier-lien {
	margin-bottom: 20px;
}

#copier-lien input {
	width: calc(100% - 68px);
}

#copier-lien span:last-child {
	margin-left: 10px;
}

#menu-partager .copier span i {
	font-size: 24px;
	width: 24px;
	height: 24px;
	cursor: pointer;
}

#menu-partager .copier span i:active {
	opacity: 0.8;
}

#menu-partager .bouton {
	width: 100%;
	text-align: center;
	margin-bottom: 20px;
}

#menu-partager .credits {
	padding-top: 10px;
	border-top: 1px solid #ddd;
}

#travaux {
	max-width: 750px;
	height: 90%;
}

#travaux .conteneur {
	padding: 0;
}

#travaux .travail {
	display: inline-block;
	width: calc(100% - 40px);
	border: 1px solid #ddd;
	border-radius: 4px;
	margin: 20px 20px 0 20px;
}

#travaux .travail:last-child {
	margin: 20px;
}

#travaux .travail .meta {
	display: block;
	padding: 10px 15px;
	margin-bottom: 10px;
	border-bottom: 1px dashed #ddd;
	line-height: 1.25;
}

#travaux .travail .meta .motdepasse {
	font-size: 13px;
}

#travaux .travail .boutons {
	padding: 0 15px 10px;
	font-size: 0;
}

#travaux .travail .bouton {
	height: 34px;
	line-height: 34px;
}

#travaux .travail .bouton.icone {
	font-size: 24px;
	font-weight: 400;
	letter-spacing: 0;
	text-indent: 0;
}

#travaux .travail .bouton.evaluer {
	background: #00a885;
	color: #fff;
}

#travaux .travail .bouton.evaluer:hover {
	background: #00896c;
}

#travaux .travail .bouton:not(.icone) {
	font-size: 11px;
}

#travaux .travail .bouton + .bouton {
	margin-left: 15px;
}

#liste-criteres {
	padding: 10px 15px;
	border-top: 1px solid #ddd;
}

#liste-criteres label {
	margin-bottom: 5px;
}

#liste-criteres .etoiles {
	margin-bottom: 10px;
}

#liste-criteres .etoiles:last-of-type {
	margin-bottom: 0;
}

#travail.deposer,
#bloc {
	height: 90%;
	max-width: 750px;
}

#travail.verifier {
	height: 202px;
}

#travail {
	max-width: 750px;
}

#travail .conteneur,
#bloc .conteneur {
	height: calc(100% - 95px);
	padding: 0 20px 20px;
}

#travail .contenu,
#bloc .contenu {
	margin-top: 20px;
}

#travaux #retroaction {
	width: 100%;
	outline: 0;
	border-top: 1px solid #ddd;
	border-bottom: 1px solid #ddd;
	margin-bottom: 10px;
}

#commentaire {
	display: block;
    width: 100%;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 7px 15px;
    line-height: 1.5;
}

#etoiles {
	margin-top: 20px;
}

#texte {
	border: 1px solid #ddd;
    width: 100%;
	outline: 0;
	border-radius: 4px;
	margin-bottom: 20px;
}

#texte.focus {
	border-color: #001d1d;
}

#date-et-horaire {
	display: flex;
	width: 100%;
}

#date {
	width: calc(33.333333333333% - 14px);
}

#debut {
	width: calc(33.333333333333% - 14px);
	margin-left: 21px;
}

#fin {
	width: calc(33.333333333333% - 14px);
	margin-left: 21px;
}

#duree {
	display: flex;
	align-items: center;
}

#duree span {
	margin-bottom: 20px;
	margin-left: 10px;
	margin-right: 10px;
}

#fichier {
	display: flex;
	margin-bottom: 20px;
}

#fichier .bouton {
	margin-bottom: 0;
}

#fichier .bouton + .bouton {
	margin-left: 20px;
}

#verrouillage,
#criteres,
#depot {
	display: flex;
	margin-bottom: 20px;
}

#verrouillage .label,
#criteres .label,
#depot .label {
	display: flex;
}

#verrouillage .label:first-child,
#criteres .label:first-child,
#depot .label:first-child {
	margin-right: 20px;
}

#verrouillage .label input,
#criteres .label input,
#depot .label input {
	margin-right: 7px;
}

#verrouillage .label label,
#criteres .label label,
#depot .label label {
	margin-bottom: 0;
}

#evaluation {
	display: flex;
	flex-wrap: wrap;
	width: 100%;
}

#evaluation label {
	display: inline-flex;
	width: calc(50% - 10px);
}

#evaluation label:first-child {
	margin-right: 20px;
}

#evaluation .critere {
	display: flex;
	width: 100%;
}

#evaluation .critere select,
#evaluation .critere input {
	display: inline-flex;
	width: calc(50% - 10px);
	margin-bottom: 10px;
}

#evaluation .critere input {
	margin-right: 20px;
}

#evaluation .actions span {
	display: inline-block;
	width: 34px;
	height: 34px;
	color: #00ced1;
	font-size: 34px;
	cursor: pointer;
	margin-bottom: 20px;
	line-height: 1;
	user-select: none;
}

#evaluation .actions span:hover,
#evaluation .actions span:active {
	opacity: 0.5;
}

#evaluation .actions span:first-child {
	color: #aaa;
	margin-right: 15px;
}

#vignette {
	margin-bottom: 5px;
}

#apercu {
	display: block;
	margin-bottom: 20px;
	font-size: 0;
}

#apercu i {
	font-size: 48px;
}

#apercu img {
	max-width: 70px;
}

#couleurs {
	display: flex;
	justify-content: flex-start;
	align-items: center;
	margin-bottom: 20px;
	overflow: auto;
}

#couleurs span {
	display: block;
	width: 30px;
	height: 30px;
	border-radius: 50%;
	margin-right: 10px;
	cursor: pointer;
	flex: 0 0 auto;
}

#couleurs span.icone {
	display: flex;
	width: 62px;
	height: 30px;
}

#couleurs span.icone label {
	font-size: 24px;
	width: 24px;
	margin-right: 8px;
	margin-bottom: 0;
	line-height: 30px;
}

#couleurs span input[type="color"] {
	width: 30px;
	height: 30px;
	padding: 0;
	border: none;
	cursor: pointer;
}

#couleurs span input[type="color"]::-moz-color-swatch {
	border: none;
	border-radius: 50%;
}

#couleurs span input[type="color"]::-webkit-color-swatch {
	border: none;
	border-radius: 50%;
}

#couleurs span input[type="color"].selectionne::-moz-color-swatch {
	border: 2px solid #000;
}

#couleurs span input[type="color"].selectionne::-webkit-color-swatch {
	border: 2px solid #000;
}

#couleurs span.selectionne {
	border: 2px solid #000;
}

#travail .contenu input:last-child {
	margin-bottom: 0;
}

#travail .bouton.large.rouge {
	margin-top: 20px;
}

#valider {
	position: absolute;
	display: flex;
	justify-content: center;
	align-items: center;
	font-weight: 700;
	font-size: 16px;
	text-transform: uppercase;
	color: #fff;
	background: #242f3d;
	border-top: 1px solid #ddd;
	bottom: 0;
	left: 0;
	right: 0;
	height: 50px;
	cursor: pointer;
	letter-spacing: 1px;
    text-indent: 1px;
	transition: all ease-in 0.1s;
	text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
	border-bottom-left-radius: 10px;
	border-bottom-right-radius: 10px;
}

#valider:hover {
	background: #394b62;
}

.modale .etoiles {
	display: flex;
	margin-bottom: 20px;
    line-height: 1;
}

.modale .etoiles .etoile {
	font-size: 24px;
	width: 24px;
	color: #fdcc33;
	cursor: pointer;
	user-select: none;
}

.modale .etoiles:last-of-type {
	margin-bottom: 0;
}

.modale .bouton.large {
	width: 100%;
	text-align: center;
	margin-bottom: 20px;
}

.modale .bouton.large:last-child {
	margin-bottom: 0;
}

.modale .bouton.rouge {
	color: #fff;
	background: #ff6259;
}

.modale .bouton.rouge:hover {
	background: #d70b00;
}

.modale .langue {
	margin-bottom: 20px;
}

.modale .langue span {
	display: inline-flex;
	width: 45px;
	height: 45px;
	justify-content: center;
	align-items: center;
	line-height: 1;
	font-size: 20px;
	border-radius: 50%;
	margin-right: 10px;
	border: 1px solid #ddd;
	cursor: pointer;
	user-select: none;
}

.modale .langue span.selectionne {
	background: #444;
	color: #fff;
	border: 1px solid #222;
}

.modale .langue span:last-child {
	margin-right: 0;
}

.modale.confirmation .conteneur {
	text-align: center;
	padding: 30px 25px;
	max-width: 500px;
}

#modale-parametres .contenu {
	font-size: 0;
}

#codeqr.modale .contenu {
	text-align: center;
	font-size: 0;
}

@media screen and (max-width: 1023px) {
	#conteneur {
		left: 0!important;
		width: 100%!important;
	}
}
</style>

<style>
#blocs .bloc .lire__link-wrap {
	display: inline-block;
    font-size: 13px;
    padding: 4px 10px;
    border: 1px solid;
	cursor: pointer;
	margin-top: 8px;
	user-select: none;
	transition: all .1s ease-in;
}

#blocs .bloc .lire__link-wrap:hover {
	background: #001d1d;
	color: #fff;
}

#blocs .bloc.section .lire__link-wrap {
	margin-bottom: 0;
}

#codeqr.modale #qr {
	display: inline-block;
}

#codeqr.modale #qr img {
	max-width: 100%;
	height: auto;
	max-height: 60vh;
}

@media screen and (max-width: 399px) {
	#date-et-horaire {
		flex-wrap: wrap;
	}

	#date {
		width: 100%!important;
	}

	#debut,
	#fin {
		width: 100%!important;
		margin-left: 0!important;
	}
}

@media screen and (max-width: 499px) {
	#travaux,
	#travail.deposer,
	#bloc {
		height: 100%!important;
		width: 100%!important;
		border-radius: 0!important;
	}

	#valider {
		border-radius: 0!important;
	}
}

@media screen and (max-width: 599px) {
	#blocs .bloc .date-et-horaire {
		flex-wrap: wrap;
	}

	#blocs .bloc:not(.section) .contenu {
		width: calc(100% - 109px)!important;
	}

	#blocs .bloc .date,
	#blocs .bloc .horaire {
		width: 100%!important;
	}

	#blocs .bloc .date {
		margin-right: 0!important;
		margin-bottom: 8px!important;
	}

	#blocs .bloc .vignette {
		padding: 5px!important;
	}

	#blocs .bloc .vignette .icone {
		font-size: 48px!important;
		padding: 0 16px!important;
	}

	#blocs .bloc .vignette .image img {
		max-width: 80px!important;
	}

	#blocs .bloc .vignette .cadenas {
		font-size: 36px!important;
		bottom: -10px!important;
		left: -13px!important;
	}

	#blocs .bloc .duree {
		font-size: 11px!important;
	}

	#blocs .bloc .titre {
		font-size: 16px!important;
	}

	#blocs .bloc .texte {
		font-size: 14px!important;
	}

	#blocs .bloc.section .texte {
		font-size: 15px!important;
	}

	#blocs .bloc .action .bouton.icone {
		padding: 0 10px!important;
	}

	#blocs .bloc .action {
		flex-wrap: wrap!important;;
	}

	#travaux .travail .meta {
		font-size: 14px!important;
	}

	#travaux .travail .meta .motdepasse {
		font-size: 12px!important;
	}
}

@media screen and (max-width: 767px) {
	.modale .langue span {
		width: 40px;
		height: 40px;
		font-size: 18px;
		margin-right: 9px;
	}
}

@media screen and (min-width: 768px) {
	#conteneur-parcours,
	#conteneur-header {
		width: 750px;
	}
}

@media screen and (min-width: 992px) {
	#conteneur-parcours,
	#conteneur-header {
		width: 970px;
	}
}

@media screen and (max-width: 320px) {
	#blocs .bloc .action .bouton.icone {
		padding: 0 5px!important;
	}

	#blocs .bloc .action .bouton + .bouton {
		margin-left: 10px!important;
	}
}
</style>
