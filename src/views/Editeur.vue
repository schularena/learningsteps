<template>
	<div id="page">
		<div id="parcours">
			<header>
				<div id="conteneur-header">
					<a id="conteneur-logo" :href="definirRacine()" title="Accueil">
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
					<label>Lien et code QR&nbsp;:</label>
					<div id="copier-lien" class="copier">
						<input type="text" disabled :value="definirRacine() + '#/s/' + id">
						<span class="icone lien" role="button" tabindex="0" title="Copier le lien"><i class="material-icons">content_copy</i></span>
						<span class="icone codeqr" role="button" tabindex="0" title="Afficher le code QR" @click="modale = 'code-qr'"><i class="material-icons">qr_code</i></span>
					</div>
					<label>Code d'intégration&nbsp;:</label>
					<div id="copier-iframe" class="copier">
						<input type="text" disabled :value="'<iframe src=&quot;' + definirRacine() + '#/s/' + id + '&quot; allowfullscreen frameborder=&quot;0&quot; width=&quot;100%&quot; height=&quot;500&quot;></iframe>'">
						<span class="icone" role="button" tabindex="0" title="Copier le code d\'intégration"><i class="material-icons">content_copy</i></span>
					</div>
					<p class="credits">Créé avec <a href="https://ladigitale.dev/digisteps/" target="_blank" rel="noreferrer"><u>Digisteps by La Digitale</u></a></p>
				</div>
			</div>

			<section>
				<div id="conteneur-parcours">
					<div id="actions" v-if="admin">
						<span id="ajouter" class="bouton" role="button" tabindex="0" @click="ouvrirModaleBloc('creation', '')">Ajouter une étape</span>
					</div>
					<draggable id="blocs" class="admin" v-model="blocs" :animation="250" :sort="true" :swap-threshold="0.5" :force-fallback="true" :fallback-tolerance="10" filter=".statique, .lire" draggable=".bloc" @end="modifierPositionBloc" v-if="blocs.length > 0 && admin">
						<template v-for="(bloc, indexBloc) in blocs" :key="'bloc_' + indexBloc">
							<article :id="bloc.id" class="bloc section" :class="{'invisible': bloc.visibilite === false}" v-if="bloc.type === 'section'">
								<div class="contenu">
									<span class="titre" :style="{'border-color': bloc.couleur}">{{ bloc.titre }}</span>
									<span class="texte" v-if="bloc.texte !== ''" v-html="bloc.texte" />
								</div>
								<div class="actions statique">
									<span @click="modifierVisibiliteBloc(bloc.id)" title="Masquer" v-if="bloc.visibilite === true"><i class="material-icons">visibility</i></span>
									<span @click="modifierVisibiliteBloc(bloc.id)" title="Afficher" v-else><i class="material-icons">visibility_off</i></span>
									<span @click="ouvrirModaleBloc('edition', bloc)" title="Éditer"><i class="material-icons">edit</i></span>
									<span @click="afficherSupprimerBloc(bloc.id)" title="Supprimer"><i class="material-icons">delete</i></span>
								</div>
							</article>
							<article :id="bloc.id" class="bloc" :class="{'invisible': bloc.visibilite === false}" :style="{'background': eclaircirCouleur(bloc.couleur)}" v-else>
								<div class="vignette" :style="{'border-color': bloc.couleur, 'color': modifierCouleur(bloc.couleur, -20)}">
									<span class="icone" v-if="bloc.vignette.substring(0, 5) === 'icone'"><i class="material-icons">{{ bloc.vignette.substring(6) }}</i></span>
									<span class="image" v-else><img :src="definirRacine() + 'static/vignettes/' + bloc.vignette + '.png'"></span>
									<span class="duree" v-if="bloc.heures > 0 || bloc.minutes > 0">{{ definirDuree(bloc.heures, bloc.minutes) }}</span>
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
									<div class="action" v-if="bloc.lien !== '' || bloc.fichier !== '' || (bloc.hasOwnProperty('depot') === true && bloc.depot === 'oui' && bloc.hasOwnProperty('travaux') === true && bloc.travaux.length > 0)">
										<a class="bouton" :href="bloc.lien" target="_blank" :style="{'border-color': bloc.couleur}" v-if="bloc.lien !== ''">Ouvrir le lien</a>
										<a class="bouton" :href="definirRacine() + 'fichiers/' + id + '/' + bloc.fichier" target="_blank" :style="{'border-color': bloc.couleur}" v-else-if="bloc.fichier !== ''">Télécharger le fichier</a>
										<span class="bouton travaux" role="button" tabindex="0" :style="{'border-color': bloc.couleur}" v-if="bloc.hasOwnProperty('depot') === true && bloc.depot === 'oui' && bloc.hasOwnProperty('travaux') === true && bloc.travaux.length > 0" @click="ouvrirModaleTravaux(bloc)">Afficher les travaux</span>
									</div>
								</div>
								<div class="actions statique">
									<span @click="modifierVisibiliteBloc(bloc.id)" title="Masquer" v-if="bloc.visibilite === true"><i class="material-icons">visibility</i></span>
									<span @click="modifierVisibiliteBloc(bloc.id)" title="Afficher" v-else><i class="material-icons">visibility_off</i></span>
									<span @click="ouvrirModaleBloc('edition', bloc)" title="Éditer"><i class="material-icons">edit</i></span>
									<span @click="afficherSupprimerBloc(bloc.id)" title="Supprimer"><i class="material-icons">delete</i></span>
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
							<article :id="bloc.id" class="bloc" :style="{'background': eclaircirCouleur(bloc.couleur)}" v-else-if="bloc.visibilite === true && bloc.type !== 'section'">
								<div class="vignette" :style="{'border-color': bloc.couleur, 'color': modifierCouleur(bloc.couleur, -20)}">
									<span class="icone" v-if="bloc.vignette.substring(0, 5) === 'icone'"><i class="material-icons">{{ bloc.vignette.substring(6) }}</i></span>
									<span class="image" v-else><img :src="definirRacine() + 'static/vignettes/' + bloc.vignette + '.png'"></span>
									<span class="duree" v-if="bloc.heures > 0 || bloc.minutes > 0">{{ definirDuree(bloc.heures, bloc.minutes) }}</span>
								</div>
								<div class="contenu" :class="bloc.type">
									<span class="titre" :style="{'border-color': bloc.couleur}">{{ bloc.titre }}</span>
									<span class="texte" v-if="bloc.texte !== ''" v-html="bloc.texte" />
									<div class="date-et-horaire" v-if="bloc.date !== '' && bloc.debut !== '' && bloc.fin !== ''">
										<span class="date"><i class="material-icons">event_note</i>{{ definirDate(bloc.date) }}</span>
										<span class="horaire"><i class="material-icons">schedule</i>{{ definirHoraire(bloc.date, bloc.debut, bloc.fin) }}</span>
									</div>
									<span class="lieu" v-if="bloc.lieu !== '' && bloc.lieu.includes('http') === false"><i class="material-icons">place</i><a :href="'https://www.openstreetmap.org/search?query=' + bloc.lieu" target="_blank">{{ bloc.lieu }}</a></span>
									<span class="lieu" v-else-if="bloc.lieu !== '' && bloc.lieu.includes('http') === true"><i class="material-icons">voice_chat</i><a :href="bloc.lieu" target="_blank">{{ definirDomaine(bloc.lieu) }}</a></span>
									<div class="action" v-if="bloc.lien !== '' || bloc.fichier !== '' || (bloc.hasOwnProperty('depot') === true && bloc.depot === 'oui')">
										<a class="bouton" :href="bloc.lien" target="_blank" :style="{'border-color': bloc.couleur}" v-if="bloc.lien !== ''">Ouvrir le lien</a>
										<a class="bouton" :href="definirRacine() + 'fichiers/' + id + '/' + bloc.fichier" target="_blank" :style="{'border-color': bloc.couleur}" v-else-if="bloc.fichier !== ''">Télécharger le fichier</a>
										<span class="bouton travaux" role="button" tabindex="0" :style="{'border-color': bloc.couleur}" v-if="bloc.hasOwnProperty('depot') === true && bloc.depot === 'oui'" @click="ouvrirModaleDepot(bloc.id)">Déposer ou afficher un travail</span>
									</div>
								</div>
							</article>
						</template>
					</div>
					<div id="blocs" class="vide" v-else-if="blocs.length === 0">
						<p>Aucune étape pour le moment.</p>
					</div>
				</div>
			</section>
		</div>

		<div class="conteneur-modale" v-if="modale === 'bloc'">
			<div id="bloc" class="modale">
				<header>
					<span class="titre" v-if="mode === 'creation' && type === '-'">Ajouter une étape</span>
					<span class="titre" v-else-if="mode === 'creation' && type === 'section'">Ajouter une section</span>
					<span class="titre" v-else-if="mode === 'creation' && type === 'seance'">Ajouter une séance</span>
					<span class="titre" v-else-if="mode === 'creation' && type === 'document'">Ajouter un document ou un média</span>
					<span class="titre" v-else-if="mode === 'creation' && type === 'activite'">Ajouter une activité ou un exercice</span>
					<span class="titre" v-else-if="mode === 'creation' && type === 'evaluation'">Ajouter une évaluation</span>
					<span class="titre" v-else>Modifier cette étape</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleContenu"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<label for="champ-titre">Titre</label>
						<input id="champ-titre" type="text" placeholder="Ajouter un titre" :value="titre" @input="titre = $event.target.value">
						<label v-if="type === '-' || type === 'section' || type === 'seance' || type === 'document'">Description</label>
						<label v-else>Consigne</label>
						<div id="texte" />
						<label>Type d'étape</label>
						<select @change="modifierType($event.target.value)">
							<option value="-" :selected="type === '-'">-</option>
							<option value="section" :selected="type === 'section'">-Section-</option>
							<option value="seance" :selected="type === 'seance'">Séance en présence ou à distance</option>
							<option value="document" :selected="type === 'document'">Document ou média</option>
							<option value="activite" :selected="type === 'activite'">Activité ou exercice</option>
							<option value="evaluation" :selected="type === 'evaluation'">Évaluation</option>
						</select>
						<div id="options" v-if="type !== '-' && type !== 'section'">
							<template v-if="type === 'seance'">
								<div id="date-et-horaire">
									<div id="date">
										<label for="champ-date">Date</label>
										<input id="champ-date" type="date" :value="date" @input="date = $event.target.value">
									</div>
									<div id="debut">
										<label for="champ-debut">Début</label>
										<input id="champ-debut" type="time" :value="debut" @input="debut = $event.target.value">
									</div>
									<div id="fin">
										<label for="champ-fin">Fin</label>
										<input id="champ-fin" type="time" :value="fin" @input="fin = $event.target.value">
									</div>
								</div>
								<label for="champ-lieu">Adresse ou lien de visioconférence</label>
								<input id="champ-lieu" type="text" placeholder="Exemple : 5 rue des fleurs, 2000 La Digitale ou https://meet.jit.si" :value="lieu" @input="lieu = $event.target.value">
							</template>
							<template v-else-if="type === 'document' || type === 'activite' || type === 'evaluation'">
								<label>Type de ressource</label>
								<select @change="modifierRessource($event.target.value)">
									<option value="-" :selected="ressource === '-'" v-if="type !== 'document'">-</option>
									<option value="lien" :selected="ressource === 'lien'">Lien</option>
									<option value="fichier" :selected="ressource === 'fichier'">Fichier</option>
								</select>
								<label for="champ-lien" v-if="ressource === 'lien'">Lien</label>
								<input id="champ-lien" type="text" placeholder="Exemples : https://ladigitale.dev/digiplay, https://ladigitale.dev/digiread" :value="lien" @input="lien = $event.target.value" v-if="type === 'document' && ressource === 'lien'">
								<input id="champ-lien" type="text" placeholder="Exemples : https://digipad.app, https://digistorm.app, https://ladigitale.dev/digiquiz" :value="lien" @input="lien = $event.target.value" v-else-if="type !== 'document' && ressource === 'lien'">
								<label v-if="ressource === 'fichier'">Fichier (max. 2 Mo)</label>
								<div id="fichier" v-if="ressource === 'fichier'">
									<a class="bouton" :href="definirRacine() + 'fichiers/' + id + '/' + fichier" target="_blank" v-if="mode === 'edition' && fichier !== '' && ancienFichier === fichier">Voir le fichier actuel</a>
									<label for="televerser" class="bouton" role="button" tabindex="0" v-if="fichier === ''">Sélectionner un fichier</label>
									<label for="televerser" class="bouton" role="button" tabindex="0" v-else-if="mode === 'creation' && fichier !== ''">{{ fichier }}</label>
									<label for="televerser" class="bouton" role="button" tabindex="0" v-else-if="mode === 'edition' && fichier !== '' && ancienFichier === fichier">Remplacer le fichier actuel</label>
									<label for="televerser" class="bouton" role="button" tabindex="0" v-else-if="mode === 'edition' && fichier !== '' && ancienFichier !== fichier">{{ fichier }}</label>
									<input id="televerser" type="file" accept=".jpg, .jpeg, .png, .gif, .mp3, .mp4, .pdf, .doc, .docx, .odt, .ppt, .pptx, .odp, .xls, .xlsx, .ods" @change="selectionnerFichier" style="display: none;">
								</div>
							</template>
							<template v-if="type === 'activite' || type === 'evaluation'">
								<label>Dépôt par les apprenants (lien ou fichier)</label>
								<div id="depot">
									<span class="label">
										<input type="radio" id="depot_oui" name="depot" value="oui" :checked="depot === 'oui'" @change="depot = $event.target.value">
										<label for="depot_oui">Oui</label>
									</span>
									<span class="label">
										<input type="radio" id="depot_non" name="depot" value="non" :checked="depot === 'non'" @change="depot = $event.target.value">
										<label for="depot_non">Non</label>
									</span>
								</div>
							</template>
							<label v-if="type === 'seance'">Durée</label>
							<label v-else-if="type === 'document'">Durée estimée de consultation</label>
							<label v-else>Durée estimée de réalisation</label>
							<div id="duree">
								<select @change="heures = parseInt($event.target.value)">
									<option value="0" :selected="heures === 0">0</option>
									<option value="1" :selected="heures === 1">1</option>
									<option value="2" :selected="heures === 2">2</option>
									<option value="3" :selected="heures === 3">3</option>
									<option value="4" :selected="heures === 4">4</option>
									<option value="5" :selected="heures === 5">5</option>
									<option value="6" :selected="heures === 6">6</option>
									<option value="7" :selected="heures === 7">7</option>
									<option value="8" :selected="heures === 8">8</option>
									<option value="9" :selected="heures === 9">9</option>
									<option value="10" :selected="heures === 10">10</option>
									<option value="11" :selected="heures === 11">11</option>
									<option value="12" :selected="heures === 12">12</option>
								</select>
								<span>h</span>
								<select @change="minutes = parseInt($event.target.value)">
									<option value="0" :selected="minutes === 0">00</option>
									<option value="5" :selected="minutes === 5">05</option>
									<option value="10" :selected="minutes === 10">10</option>
									<option value="15" :selected="minutes === 15">15</option>
									<option value="20" :selected="minutes === 20">20</option>
									<option value="25" :selected="minutes === 25">25</option>
									<option value="30" :selected="minutes === 30">30</option>
									<option value="35" :selected="minutes === 35">35</option>
									<option value="40" :selected="minutes === 40">40</option>
									<option value="45" :selected="minutes === 45">45</option>
									<option value="50" :selected="minutes === 50">50</option>
									<option value="55" :selected="minutes === 55">55</option>
								</select>
							</div>
						</div>
						<label v-if="type !== '-' && type !== 'section'">Vignette</label>
						<select id="vignette" @change="modifierVignette($event.target.value)" v-if="type === 'seance'">
							<option value="icone_meeting_room" :selected="vignette === 'icone_meeting_room'">Icône classe</option>
							<option value="icone_devices" :selected="vignette === 'icone_devices'">Icône visioconférence</option>
							<option value="jitsi" :selected="vignette === 'jitsi'">Jitsi Meet</option>
							<option value="teams" :selected="vignette === 'teams'">Microsoft Teams</option>
							<option value="zoom" :selected="vignette === 'zoom'">Zoom</option>
							<option value="meet" :selected="vignette === 'meet'">Google Meet</option>
						</select>
						<select id="vignette" @change="modifierVignette($event.target.value)" v-else-if="type === 'document'">
							<option value="icone_article" :selected="vignette === 'icone_article'">Icône document</option>
							<option value="icone_cloud_download" :selected="vignette === 'icone_cloud_download'">Icône nuage</option>
							<option value="icone_explore" :selected="vignette === 'icone_explore'">Icône explorer</option>
							<option value="ladigitale" :selected="vignette === 'ladigitale'">La Digitale</option>
							<option value="wikipedia" :selected="vignette === 'wikipedia'">Wikipedia</option>
							<option value="peertube" :selected="vignette === 'peertube'">Peertube</option>
							<option value="pdf" :selected="vignette === 'pdf'">Fichier PDF</option>
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
						<select id="vignette" @change="modifierVignette($event.target.value)" v-else-if="type === 'activite'">
							<option value="icone_check_circle" :selected="vignette === 'icone_check_circle'">Icône activité</option>
							<option value="ladigitale" :selected="vignette === 'ladigitale'">La Digitale</option>
							<option value="h5p" :selected="vignette === 'h5p'">H5P</option>
							<option value="vocaroo" :selected="vignette === 'vocaroo'">Vocaroo</option>
							<option value="quizlet" :selected="vignette === 'quizlet'">Quizlet</option>
							<option value="flipgrid" :selected="vignette === 'flipgrid'">Flipgrid</option>
							<option value="genially" :selected="vignette === 'genially'">Genially</option>
						</select>
						<select id="vignette" @change="modifierVignette($event.target.value)" v-else-if="type === 'evaluation'">
							<option value="icone_assessment" :selected="vignette === 'icone_assessment'">Icône évaluation</option>
							<option value="ladigitale" :selected="vignette === 'ladigitale'">La Digitale</option>
							<option value="h5p" :selected="vignette === 'h5p'">H5P</option>
							<option value="vocaroo" :selected="vignette === 'vocaroo'">Vocaroo</option>
							<option value="quizlet" :selected="vignette === 'quizlet'">Quizlet</option>
							<option value="flipgrid" :selected="vignette === 'flipgrid'">Flipgrid</option>
							<option value="genially" :selected="vignette === 'genially'">Genially</option>
						</select>
						<div id="apercu" v-if="type !== '-' && type !== 'section'">
							<i class="material-icons" v-if="vignette.substring(0, 5) === 'icone'">{{ vignette.substring(6) }}</i>
							<img :src="definirRacine() + 'static/vignettes/' + vignette + '.png'" v-else>
						</div>
						<label>Couleur</label>
						<div id="couleurs">
							<span v-for="(item, index) in couleurs" :class="{'selectionne': item === couleur}" :style="{'background': item}" @click="selectionnerCouleur(item)" :key="'couleur_' + index" />
							<span class="icone">
								<label for="couleur"><i class="material-icons">colorize</i></label>
								<input type="color" id="couleur" value="#dddddd" title="Sélectionner une couleur" @change="selectionnerCouleur($event.target.value)" v-if="couleurs.includes(couleur)">
								<input type="color" id="couleur" class="selectionne" title="Sélectionner une couleur" @change="selectionnerCouleur($event.target.value)" v-else>
							</span>
						</div>
					</div>
				</div>
				<div id="valider" role="button" tabindex="0" @click="ajouterBloc" v-if="mode === 'creation'">Valider</div>
				<div id="valider" role="button" tabindex="0" @click="modifierBloc" v-else>Modifier</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'televerser'">
			<div class="modale">
				<header>
					<span class="titre">Téléversement du fichier...</span>
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
						<p>Souhaitez-vous vraiment supprimer cette étape&nbsp;?</p>
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="modale = ''">Non</span>
							<span class="bouton" role="button" tabindex="0" @click="supprimerBloc">Oui</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'travaux'">
			<div id="travaux" class="modale">
				<header>
					<span class="titre">Afficher les travaux</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleTravaux"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<div class="travail" v-for="(travail, indexTravail) in travaux" :key="'travail_' + indexTravail">
							<span class="meta">par <b>{{ travail.pseudo }}</b> {{ definirDateEtHeure(travail.date) }} (mot de passe&nbsp;: {{ travail.motdepasse }})</span>
							<a class="bouton" :href="travail.lien" target="_blank" v-if="travail.lien !== ''">Ouvrir le lien</a>
							<a class="bouton" :href="definirRacine() + 'fichiers/' + id + '/' + travail.fichier" target="_blank" v-else-if="travail.fichier !== ''">Télécharger le fichier</a>
						</div>
					</div>
				</div>
				<div id="valider" role="button" tabindex="0" @click="deposer" v-if="mode === 'deposer' || mode === 'afficher'">Valider</div>
				<div id="valider" role="button" tabindex="0" @click="verifier" v-else-if="mode === 'verifier'">Valider</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'depot'">
			<div id="travail" class="modale" :class="{'deposer': mode === 'deposer' || mode === 'afficher', 'verifier': mode === 'verifier'}">
				<header>
					<span class="titre" v-if="mode === 'deposer'">Déposer un travail</span>
					<span class="titre" v-else-if="mode === 'verifier' || mode === 'afficher'">Consulter un travail</span>
					<span class="titre" v-else>Déposer ou consulter un travail</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleDepot"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<label v-if="mode === '' || mode === '-'">Je veux...</label>
						<select @change="modifierModeDepot($event.target.value)" v-if="mode === '' || mode === '-'">
							<option value="-">-</option>
							<option value="deposer">déposer un travail.</option>
							<option value="verifier">consulter un travail.</option>
						</select>
						<template v-if="mode === 'deposer'">
							<label>Nom ou pseudo</label>
							<input type="text" :value="pseudo" placeholder="Nom ou pseudo"  @input="pseudo = $event.target.value">
							<label>Mot de passe (<u>à noter</u> pour modifier le dépôt ou consulter le retour de l'enseignant)</label>
							<input type="text" :value="motdepasse" disabled>
							<label>Type de dépôt</label>
							<select @change="modifierRessourceDepot($event.target.value)">
								<option value="lien" :selected="ressource === 'lien'">Lien</option>
								<option value="fichier" :selected="ressource === 'fichier'">Fichier</option>
							</select>
							<label for="champ-lien" v-if="ressource === 'lien'">Lien</label>
							<input id="champ-lien" type="text" :value="lien" placeholder="https://..." @input="lien = $event.target.value" v-if="ressource === 'lien'">
							<label v-if="ressource === 'fichier'">Fichier (max. 2 Mo)</label>
							<div id="fichier" v-if="ressource === 'fichier'">
								<label for="televerser" class="bouton" role="button" tabindex="0" v-if="fichier === ''">Sélectionner un fichier</label>
								<label for="televerser" class="bouton" role="button" tabindex="0" v-else>{{ fichier }}</label>
								<input id="televerser" type="file" accept=".jpg, .jpeg, .png, .gif, .mp3, .mp4, .pdf, .doc, .docx, .odt, .ppt, .pptx, .odp, .xls, .xlsx, .ods" @change="selectionnerFichier" style="display: none;">
							</div>
						</template>
						<template v-else-if="mode === 'verifier'">
							<label>Mot de passe</label>
							<input type="text" :value="motdepasse" @input="motdepasse = $event.target.value" @keydown.enter="verifier">
						</template>
						<template v-else-if="mode === 'afficher'">
							<label>Nom ou pseudo</label>
							<input type="text" :value="pseudo" placeholder="Nom ou pseudo"  @input="pseudo = $event.target.value">
							<label>Mot de passe</label>
							<input type="text" :value="motdepasse" disabled>
							<label>Type de dépôt</label>
							<select @change="modifierRessourceDepot($event.target.value)">
								<option value="lien" :selected="ressource === 'lien'">Lien</option>
								<option value="fichier" :selected="ressource === 'fichier'">Fichier</option>
							</select>
							<label for="champ-lien" v-if="ressource === 'lien'">Lien</label>
							<input id="champ-lien" type="text" :value="lien" placeholder="https://..." @input="lien = $event.target.value" v-if="ressource === 'lien'">
							<label v-if="ressource === 'fichier'">Fichier (max. 2 Mo)</label>
							<div id="fichier" v-if="ressource === 'fichier'">
								<a class="bouton" :href="definirRacine() + 'fichiers/' + id + '/' + fichier" target="_blank" v-if="fichier !== '' && ancienFichier === fichier">Voir le fichier actuel</a>
								<label for="televerser" class="bouton" role="button" tabindex="0" v-if="fichier === ''">Sélectionner un fichier</label>
								<label for="televerser" class="bouton" role="button" tabindex="0" v-else-if="fichier !== '' && ancienFichier === fichier">Remplacer le fichier actuel</label>
								<label for="televerser" class="bouton" role="button" tabindex="0" v-else-if="fichier !== '' && ancienFichier !== fichier">{{ fichier }}</label>
								<input id="televerser" type="file" accept=".jpg, .jpeg, .png, .gif, .mp3, .mp4, .pdf, .doc, .docx, .odt, .ppt, .pptx, .odp, .xls, .xlsx, .ods" @change="selectionnerFichier" style="display: none;">
							</div>
							<label>Rétroaction</label>
							<div id="retroaction" v-if="retroaction !== ''" v-html="retroaction" />
							<div id="retroaction" v-else>Aucune rétroaction.</div>
						</template>
					</div>
				</div>
				<div id="valider" role="button" tabindex="0" @click="deposer" v-if="mode === 'deposer' || mode === 'afficher'">Valider</div>
				<div id="valider" role="button" tabindex="0" @click="verifier" v-else-if="mode === 'verifier'">Valider</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'connexion'">
			<div class="modale">
				<header>
					<span class="titre">Modifier ce parcours</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleConnexion"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<label>Question secrète</label>
						<select :value="question" @change="question = $event.target.value">
							<option v-for="(item, index) in questions" :key="'option_' + index">{{ item }}</option>
						</select>
						<label>Réponse secrète</label>
						<input type="password" :value="reponse" @input="reponse = $event.target.value" @keydown.enter="debloquerParcours">
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="debloquerParcours">Valider</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'parcours'">
			<div class="modale">
				<header>
					<span class="titre">Paramètres du parcours</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleParcours"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<span class="bouton large" role="button" tabindex="0" @click="ouvrirModaleNomParcours">Modifier le nom du parcours</span>
						<span class="bouton large" role="button" tabindex="0" @click="ouvrirModaleAccesParcours">Modifier l'accès au parcours</span>
						<span class="bouton large rouge" role="button" tabindex="0" @click="afficherSupprimerParcours">Supprimer le parcours</span>
						<span class="bouton large" role="button" tabindex="0" @click="terminerSession">Terminer la session d'édition</span>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'modifier-nom'">
			<div class="modale">
				<header>
					<span class="titre">Modifier le nom du parcours</span>
					<span class="fermer" role="button" @click="fermerModaleNomParcours"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<label>Nouveau nom</label>
						<input type="text" :value="nouveaunom" @input="nouveaunom = $event.target.value" @keydown.enter="modifierNomParcours">
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="modifierNomParcours">Modifier</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'modifier-acces'">
			<div class="modale">
				<header>
					<span class="titre">Modifier l'accès au parcours</span>
					<span class="fermer" role="button" tabindex="0" @click="fermerModaleAccesParcours"><i class="material-icons">close</i></span>
				</header>
				<div class="conteneur">
					<div class="contenu">
						<label>Question secrète actuelle</label>
						<select :value="question" @change="question = $event.target.value">
							<option v-for="(item, index) in questions" :key="'option_' + index">{{ item }}</option>
						</select>
						<label>Réponse secrète actuelle</label>
						<input type="text" :value="reponse" @input="reponse = $event.target.value">
						<label>Nouvelle question secrète</label>
						<select :value="nouvellequestion" @change="nouvellequestion = $event.target.value">
							<option v-for="(item, index) in questions" :key="'option_' + index">{{ item }}</option>
						</select>
						<label>Nouvelle réponse secrète</label>
						<input type="text" :value="nouvellereponse" @input="nouvellereponse = $event.target.value" @keydown.enter="modifierAccesParcours">
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="modifierAccesParcours">Modifier</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-else-if="modale === 'supprimer-parcours'">
			<div class="modale confirmation">
				<div class="conteneur entier">
					<div class="contenu">
						<p>Souhaitez-vous vraiment supprimer ce parcours et tout son contenu&nbsp;?</p>
						<div class="actions">
							<span class="bouton" role="button" tabindex="0" @click="modale = ''">Non</span>
							<span class="bouton" role="button" tabindex="0" @click="supprimerParcours">Oui</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="conteneur-modale" v-show="modale === 'code-qr'">
			<div id="codeqr" class="modale">
				<header>
					<span class="titre">Code QR</span>
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
import moment from 'moment'
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
			admin: false,
			id: '',
			question: '',
			questions: ['Quel est mon mot préféré ?', 'Quel est mon film préféré ?', 'Quelle est ma chanson préférée ?', 'Quel est le prénom de ma mère ?', 'Quel est le prénom de mon père ?', 'Quel est le nom de ma rue ?', 'Quel est le nom de mon employeur ?', 'Quel est le nom de mon animal de compagnie ?'],
			reponse: '',
			nom: '',
			nouveaunom: '',
			nouvellequestion: '',
			nouvellereponse: '',
			blocs: [],
			mode: 'creation',
			bloc: '',
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
			pseudo: '',
			motdepasse: '',
			vignette: '',
			heures: 0,
			minutes: 0,
			couleur: '#00ced1',
			couleurs: ['#00ced1', '#55efc4', '#74b9ff', '#a29bfe', '#ffeaa7', '#fab1a0', '#fea7c6'],
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
		let session = ''
		if (localStorage.getItem('session')) {
			session = localStorage.getItem('session')
		}
		if (localStorage.getItem('selection')) {
			this.selection = JSON.parse(localStorage.getItem('selection'))
		}
		const xhr = new XMLHttpRequest()
		xhr.onload = function () {
			if (xhr.readyState === xhr.DONE && xhr.status === 200) {
				try {
					JSON.parse(xhr.responseText)
				}
				catch (_) {
					this.$router.push('/')
				}
				const reponse = JSON.parse(xhr.responseText)
				if (!reponse.nom || reponse.nom === '') {
					this.$router.push('/')
				} else {
					this.admin = reponse.admin
					this.nom = reponse.nom
					if (reponse.donnees !== '') {
						const donnees = JSON.parse(reponse.donnees)
						this.blocs = donnees.blocs
					}
					setTimeout(function () {
						document.title = this.nom + ' - Digisteps by La Digitale'
						this.$parent.$parent.chargement = false
						const textes = document.querySelectorAll('.texte')
						textes.forEach(function (texte) {
							if (texte.scrollHeight > texte.clientHeight) {
								const span = document.createElement('span')
								span.classList.add('lire')
								span.textContent = 'Lire la suite'
								texte.insertAdjacentElement('afterend', span)
							}
						})
						const balises = document.querySelectorAll('.lire')
						balises.forEach(function (balise) {
							balise.addEventListener('click', function () {
								const id = balise.parentNode.parentNode.id
								if (document.querySelector('#' + id + ' .texte').style.display === 'block') {
									document.querySelector('#' + id + ' .texte').style.display = '-webkit-box'
									balise.textContent = 'Lire la suite'
								} else {
									document.querySelector('#' + id + ' .texte').style.display = 'block'
									balise.textContent = 'Réduire'
								}
							})
						})
					}.bind(this), 300)
				}
			} else {
				this.$parent.$parent.chargement = false
				this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
			}
		}.bind(this)
		xhr.open('POST', this.$parent.$parent.hote + '/inc/recuperer_parcours.php', true)
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
		xhr.send('id=' + this.id + '&session=' + session)
	},
	mounted () {
		const lien = this.definirRacine() + '#/s/' + this.id
		const clipboardLien = new ClipboardJS('#copier-lien .lien', {
			text: function () {
				return lien
			}
		})
		clipboardLien.on('success', function () {
			this.$parent.$parent.notification = 'Lien copié dans le presse-papier.'
		}.bind(this))
		const iframe = '<iframe src="' + lien + '" allowfullscreen frameborder="0" width="100%" height="500"></iframe>'
		const clipboardIframe = new ClipboardJS('#copier-iframe span', {
			text: function () {
				return iframe
			}
		})
		clipboardIframe.on('success', function () {
			this.$parent.$parent.notification = 'Code d\'intégration copié dans le presse-papier.'
		}.bind(this))
		// eslint-disable-next-line
		this.codeqr = new QRCode('qr', {
			text: lien,
			width: 500,
			height: 500,
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
				this.bloc = item.id
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
				if (item.hasOwnProperty('depot') === true) {
					this.depot = item.depot
				}
				if (item.hasOwnProperty('travaux') === true) {
					this.travaux = item.travaux
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
				document.querySelector('#texte').innerHTML = ''
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
						{ name: 'gras', title: 'Gras', icon: '<i class="material-icons">format_bold</i>', result: () => pell.exec('bold') },
						{ name: 'italique', title: 'Italique', icon: '<i class="material-icons">format_italic</i>', result: () => pell.exec('italic') },
						{ name: 'souligne', title: 'Souligné', icon: '<i class="material-icons">format_underlined</i>', result: () => pell.exec('underline') },
						{ name: 'barre', title: 'Barré', icon: '<i class="material-icons">format_strikethrough</i>', result: () => pell.exec('strikethrough') },
						{ name: 'listeordonnee', title: 'Liste ordonnée', icon: '<i class="material-icons">format_list_numbered</i>', result: () => pell.exec('insertOrderedList') },
						{ name: 'liste', title: 'Liste', icon: '<i class="material-icons">format_list_bulleted</i>', result: () => pell.exec('insertUnorderedList') },
						{ name: 'couleur', title: 'Couleur du texte', icon: '<label for="couleur-texte"><i class="material-icons">format_color_text</i></label><input id="couleur-texte" type="color" style="display: none;">', result: () => undefined },
						{ name: 'lien', title: 'Lien', icon: '<i class="material-icons">link</i>', result: () => { const url = window.prompt('Adresse du lien'); if (url) { pell.exec('createLink', url) } } }
					],
					classes: { actionbar: 'boutons-editeur', button: 'bouton-editeur', content: 'contenu-editeur', selected: 'bouton-actif' }
				})
				editeur.content.innerHTML = this.texte
				editeur.onpaste = function (event) {
					event.preventDefault()
					event.stopPropagation()
					const texte = event.clipboardData.getData('text/plain')
					pell.exec('insertText', texte)
				}
				document.querySelector('#texte .contenu-editeur').addEventListener('focus', function () {
					document.querySelector('#texte').classList.add('focus')
				})
				document.querySelector('#texte .contenu-editeur').addEventListener('blur', function () {
					document.querySelector('#texte').classList.remove('focus')
				})
				document.querySelector('#couleur-texte').addEventListener('change', this.modifierCouleurTexte)
			})
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
				this.heures = 0
				this.minutes = 0
				this.vignette = ''
				break
			case 'seance':
				this.ressource = '-'
				this.lien = ''
				this.fichier = ''
				this.depot = 'non'
				this.vignette = 'icone_meeting_room'
				break
			case 'document':
				this.date = ''
				this.debut = ''
				this.fin = ''
				this.lieu = ''
				this.ressource = 'lien'
				this.depot = 'non'
				this.vignette = 'icone_article'
				break
			case 'activite':
				this.date = ''
				this.debut = ''
				this.fin = ''
				this.lieu = ''
				this.vignette = 'icone_check_circle'
				break
			case 'evaluation':
				this.date = ''
				this.debut = ''
				this.fin = ''
				this.lieu = ''
				this.vignette = 'icone_assessment'
				break
			}
		},
		modifierRessource (ressource) {
			this.ressource = ressource
			this.lien = ''
			this.fichier = ''
		},
		modifierVignette (vignette) {
			this.vignette = vignette
		},
		modifierCouleurTexte (event) {
			pell.exec('foreColor', event.target.value)
			document.querySelector('#texte .bouton-editeur label[for="couleur-texte"]').style.color = event.target.value
		},
		selectionnerFichier (event) {
			this.fichier = event.target.files[0].name
		},
		selectionnerCouleur (couleur) {
			this.couleur = couleur
		},
		verifierBloc () {
			if (this.titre === '') {
				this.$parent.$parent.message = 'Veuillez donner un titre à cette étape.'
				return false
			} else if (this.type === '-') {
				this.$parent.$parent.message = 'Veuillez choisir un type d\'étape.'
				return false
			} else if (this.type === 'seance' && (this.date === '' || this.debut === '' || this.fin === '' || this.lieu === '')) {
				if (this.date === '') {
					this.$parent.$parent.message = 'Veuillez indiquer une date.'
				} else if (this.debut === '') {
					this.$parent.$parent.message = 'Veuillez indiquer un horaire de début.'
				} else if (this.fin === '') {
					this.$parent.$parent.message = 'Veuillez indiquer un horaire de fin.'
				} else if (this.lieu === '') {
					this.$parent.$parent.message = 'Veuillez indiquer une adresse ou un lien de visioconférence.'
				}
				return false
			} else if ((this.type === 'document' || this.type === 'activite' || this.type === 'evaluation') && (this.ressource === 'lien' && this.lien !== '' && this.verifierLien(this.lien) === false)) {
				this.$parent.$parent.message = 'Veuillez indiquer un lien valide.'
				return false
			} else if ((this.type === 'document' || this.type === 'activite' || this.type === 'evaluation') && (this.ressource !== '-' && this.lien === '' && this.fichier === '')) {
				if (this.ressource === 'lien' && this.lien === '') {
					this.$parent.$parent.message = 'Veuillez indiquer un lien.'
				} else if (this.ressource === 'fichier' && this.fichier === '') {
					this.$parent.$parent.message = 'Veuillez sélectionner un fichier.'
				}
				return false
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
								this.modale = 'bloc'
								this.$parent.$parent.message = 'Erreur lors du téléversement du fichier.'
								resolve('erreur')
							} else {
								this.modale = ''
								this.ancienFichier = ''
								resolve(xhr.responseText)
							}
						} else {
							this.$parent.$parent.message = 'Erreur lors du téléversement du fichier.'
							resolve('erreur')
						}
					}.bind(this)
					xhr.upload.onprogress = function (e) {
						if (e.lengthComputable) {
							const pourcentage = (e.loaded / e.total) * 100
							this.progression = Math.round(pourcentage)
						}
					}.bind(this)
					xhr.open('POST', this.$parent.$parent.hote + '/inc/televerser_fichier.php', true)
					xhr.send(formData)
				} else {
					this.modale = 'bloc'
					this.$parent.$parent.message = 'La taille maximale autorisée est 2 Mo.'
					resolve('erreur')
				}
			}.bind(this))
		},
		async ajouterBloc () {
			if (this.verifierBloc() === false) {
				return false
			}
			if (this.ressource === 'fichier' && this.fichier !== '') {
				this.fichier = await this.televerserFichier()
				if (this.fichier === 'erreur') {
					return false
				}
			}
			this.$parent.$parent.chargement = true
			let session = ''
			if (localStorage.getItem('session')) {
				session = localStorage.getItem('session')
			}
			const id = 'etape-' + (new Date()).getTime() + Math.random().toString(16).slice(10)
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			const bloc = { id: id, titre: this.titre, texte: this.texte, type: this.type, date: this.date, debut: this.debut, fin: this.fin, lieu: this.lieu, lien: this.lien, fichier: this.fichier, depot: this.depot, travaux: this.travaux, vignette: this.vignette, heures: this.heures, minutes: this.minutes, couleur: this.couleur, visibilite: true }
			blocs.push(bloc)
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					this.fermerModaleContenu()
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
					} else if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = 'Vous n\'êtes pas autorisé à modifier ce parcours.'
					} else if (xhr.responseText === 'parcours_modifie') {
						this.blocs.push(bloc)
						this.$parent.$parent.notification = 'Étape ajoutée.'
						this.$nextTick(function () {
							const texte = document.querySelector('#' + id + ' .texte')
							if (texte && texte.scrollHeight > texte.clientHeight) {
								const span = document.createElement('span')
								span.classList.add('lire')
								span.textContent = 'Lire la suite'
								texte.insertAdjacentElement('afterend', span)
							}
							const balise = document.querySelector('#' + id + ' .lire')
							if (balise) {
								balise.addEventListener('click', function () {
									if (texte.style.display === 'block') {
										texte.style.display = '-webkit-box'
										balise.textContent = 'Lire la suite'
									} else {
										texte.style.display = 'block'
										balise.textContent = 'Réduire'
									}
								})
							}
						})
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleContenu()
					this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + '/inc/modifier_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }), session: session }
			xhr.send(JSON.stringify(json))
		},
		async modifierBloc () {
			if (this.verifierBloc() === false) {
				return false
			}
			if (this.ressource === 'fichier' && this.fichier !== this.ancienFichier) {
				this.fichier = await this.televerserFichier()
				if (this.fichier === 'erreur') {
					return false
				}
			}
			let fichier = ''
			if (this.lien !== '' && this.ancienFichier !== '') {
				fichier = this.ancienFichier
			}
			this.$parent.$parent.chargement = true
			let session = ''
			if (localStorage.getItem('session')) {
				session = localStorage.getItem('session')
			}
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			blocs.forEach(function (bloc, index) {
				if (bloc.id === this.bloc) {
					blocs.splice(index, 1, { id: bloc.id, titre: this.titre, texte: this.texte, type: this.type, date: this.date, debut: this.debut, fin: this.fin, lieu: this.lieu, lien: this.lien, fichier: this.fichier, depot: this.depot, travaux: this.travaux, vignette: this.vignette, heures: this.heures, minutes: this.minutes, couleur: this.couleur, visibilite: bloc.visibilite })
				}
			}.bind(this))
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					this.fermerModaleContenu()
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
					} else if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = 'Vous n\'êtes pas autorisé à modifier ce parcours.'
					} else if (xhr.responseText === 'parcours_modifie') {
						this.blocs = blocs
						this.$parent.$parent.notification = 'Étape modifiée.'
						this.$nextTick(function () {
							const texte = document.querySelector('#' + this.bloc + ' .texte')
							const balise = document.querySelector('#' + this.bloc + ' .lire')
							if (texte && texte.scrollHeight > texte.clientHeight && !balise) {
								const span = document.createElement('span')
								span.classList.add('lire')
								span.textContent = 'Lire la suite'
								texte.insertAdjacentElement('afterend', span)
							}
							if (texte && texte.scrollHeight === texte.clientHeight && balise) {
								balise.parentNode.removeChild(balise)
							}
							if (balise) {
								balise.addEventListener('click', function () {
									if (texte.style.display === 'block') {
										texte.style.display = '-webkit-box'
										balise.textContent = 'Lire la suite'
									} else {
										texte.style.display = 'block'
										balise.textContent = 'Réduire'
									}
								})
							}
						}.bind(this))
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleContenu()
					this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + '/inc/modifier_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }), session: session, fichier: fichier }
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
			this.vignette = ''
			this.heures = 0
			this.minutes = 0
			this.couleur = '#00ced1'
			this.progression = 0
		},
		modifierPositionBloc () {
			if (this.blocs.length > 1) {
				this.$parent.$parent.chargementBlocs = true
				let session = ''
				if (localStorage.getItem('session')) {
					session = localStorage.getItem('session')
				}
				const xhr = new XMLHttpRequest()
				xhr.onload = function () {
					if (xhr.readyState === xhr.DONE && xhr.status === 200) {
						this.$parent.$parent.chargementBlocs = false
						if (xhr.responseText === 'non_autorise') {
							this.$parent.$parent.message = 'Vous n\'êtes pas autorisé à modifier ce parcours.'
						}
					} else {
						this.$parent.$parent.chargementBlocs = false
						this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
					}
				}.bind(this)
				xhr.open('POST', this.$parent.$parent.hote + '/inc/modifier_parcours.php', true)
				xhr.setRequestHeader('Content-type', 'application/json')
				const json = { parcours: this.id, donnees: JSON.stringify({ blocs: this.blocs }), session: session }
				xhr.send(JSON.stringify(json))
			}
		},
		modifierVisibiliteBloc (id) {
			this.$parent.$parent.chargement = true
			let session = ''
			if (localStorage.getItem('session')) {
				session = localStorage.getItem('session')
			}
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
						this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
					} else if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = 'Vous n\'êtes pas autorisé à modifier ce parcours.'
					} else if (xhr.responseText === 'parcours_modifie') {
						this.blocs = blocs
						this.$parent.$parent.notification = 'Visibilité de l\'étape modifiée.'
					}
				} else {
					this.$parent.$parent.chargement = false
					this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + '/inc/modifier_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }), session: session }
			xhr.send(JSON.stringify(json))
		},
		afficherSupprimerBloc (id) {
			this.bloc = id
			this.modale = 'supprimer-bloc'
		},
		supprimerBloc () {
			this.modale = ''
			this.$parent.$parent.chargement = true
			let session = ''
			if (localStorage.getItem('session')) {
				session = localStorage.getItem('session')
			}
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			let fichier = ''
			blocs.forEach(function (item, index) {
				if (item.id === this.bloc) {
					if (item.fichier !== '') {
						fichier = item.fichier
					}
					blocs.splice(index, 1)
				}
			}.bind(this))
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
					} else if (xhr.responseText === 'non_autorise') {
						this.$parent.$parent.message = 'Vous n\'êtes pas autorisé à modifier ce parcours.'
					} else if (xhr.responseText === 'parcours_modifie') {
						this.blocs = blocs
						this.$parent.$parent.notification = 'Étape supprimée.'
					}
				} else {
					this.$parent.$parent.chargement = false
					this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + '/inc/modifier_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }), session: session, fichier: fichier }
			xhr.send(JSON.stringify(json))
		},
		ouvrirModaleTravaux (bloc) {
			this.bloc = bloc.id
			this.travaux = bloc.travaux
			this.modale = 'travaux'
		},
		evaluer () {
			//
		},
		fermerModaleTravaux () {
			this.modale = ''
			this.bloc = ''
			this.travaux = []
		},
		ouvrirModaleDepot (id) {
			this.bloc = id
			this.mode = '-'
			this.modale = 'depot'
		},
		modifierModeDepot (mode) {
			this.mode = mode
			if (mode === 'deposer') {
				this.ressource = 'lien'
				this.motdepasse = Math.floor(1000 + Math.random() * 9000)
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
				this.$parent.$parent.message = 'Veuillez indiquer un nom ou un pseudo.'
				return false
			} else if (this.ressource === 'lien' && this.lien !== '' && this.verifierLien(this.lien) === false) {
				this.$parent.$parent.message = 'Veuillez indiquer un lien valide.'
				return false
			} else if (this.lien === '' && this.fichier === '') {
				if (this.ressource === 'lien' && this.lien === '') {
					this.$parent.$parent.message = 'Veuillez indiquer un lien.'
				} else if (this.ressource === 'fichier' && this.fichier === '') {
					this.$parent.$parent.message = 'Veuillez sélectionner un fichier.'
				}
				return false
			}
			if (this.ressource === 'fichier' && this.fichier !== '') {
				this.fichier = await this.televerserFichier()
				if (this.fichier === 'erreur') {
					return false
				}
			}
			let fichier = ''
			if (this.lien !== '' && this.ancienFichier !== '') {
				fichier = this.ancienFichier
			}
			const blocs = JSON.parse(JSON.stringify(this.blocs))
			if (this.mode === 'deposer') {
				blocs.forEach(function (bloc) {
					if (bloc.id === this.bloc) {
						bloc.travaux.push({ motdepasse: this.motdepasse, pseudo: this.pseudo, lien: this.lien, fichier: this.fichier, retroaction: '', date: moment().format() })
					}
				}.bind(this))
			} else if (this.mode === 'afficher') {
				blocs.forEach(function (bloc) {
					if (bloc.id === this.bloc) {
						bloc.travaux.forEach(function (travail) {
							if (parseInt(travail.motdepasse) === parseInt(this.motdepasse)) {
								travail.pseudo = this.pseudo
								travail.lien = this.lien
								travail.fichier = this.fichier
								travail.date = moment().format()
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
						this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
					} else if (xhr.responseText === 'travail_depose') {
						this.blocs = blocs
						this.$parent.$parent.notification = 'Travail déposé.'
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleDepot()
					this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + '/inc/deposer_travail.php', true)
			xhr.setRequestHeader('Content-type', 'application/json')
			const json = { parcours: this.id, donnees: JSON.stringify({ blocs: blocs }), fichier: fichier }
			xhr.send(JSON.stringify(json))
		},
		verifier () {
			if (this.motdepasse === '') {
				this.$parent.$parent.message = 'Veuillez indiquer un mot de passe.'
				return false
			}
			this.blocs.forEach(function (item) {
				if (item.id === this.bloc) {
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
							motdepasseExiste = true
						}
					}.bind(this))
					if (motdepasseExiste === false) {
						this.$parent.$parent.message = 'Ce mot de passe n\'existe pas.'
					}
				}
			}.bind(this))
		},
		fermerModaleDepot () {
			this.bloc = ''
			this.pseudo = ''
			this.motdepasse = ''
			this.lien = ''
			this.fichier = ''
			this.ancienFichier = ''
			this.mode = ''
			this.ressource = '-'
			this.retroaction = ''
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
			if (this.nouveaunom !== '') {
				this.$parent.$parent.chargement = true
				let session = ''
				if (localStorage.getItem('session')) {
					session = localStorage.getItem('session')
				}
				const xhr = new XMLHttpRequest()
				xhr.onload = function () {
					if (xhr.readyState === xhr.DONE && xhr.status === 200) {
						this.$parent.$parent.chargement = false
						this.fermerModaleNomParcours()
						if (xhr.responseText === 'erreur') {
							this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
						} else if (xhr.responseText === 'non_autorise') {
							this.$parent.$parent.message = 'Vous n\'êtes pas autorisé à modifier ce parcours.'
						} else if (xhr.responseText === 'nom_modifie') {
							this.nom = this.nouveaunom
							this.$parent.$parent.notification = 'Nom modifié.'
							document.title = this.nom + ' - Digisteps by La Digitale'
						}
					} else {
						this.$parent.$parent.chargement = false
						this.fermerModaleNomParcours()
						this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
					}
				}.bind(this)
				xhr.open('POST', this.$parent.$parent.hote + '/inc/modifier_nom_parcours.php', true)
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
				xhr.send('parcours=' + this.id + '&nouveaunom=' + this.nouveaunom + '&session=' + session)
			} else {
				if (this.nouveaunom === '') {
					this.$parent.$parent.message = 'Veuillez compléter le champ «&nbsp;Nouveau nom&nbsp;».'
				}
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
				let session = ''
				if (localStorage.getItem('session')) {
					session = localStorage.getItem('session')
				}
				const xhr = new XMLHttpRequest()
				xhr.onload = function () {
					if (xhr.readyState === xhr.DONE && xhr.status === 200) {
						this.$parent.$parent.chargement = false
						this.fermerModaleAccesParcours()
						if (xhr.responseText === 'erreur') {
							this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
						} else if (xhr.responseText === 'non_autorise') {
							this.$parent.$parent.message = 'La question et la réponse secrètes ne sont pas correctes.'
						} else if (xhr.responseText === 'acces_modifie') {
							this.$parent.$parent.notification = 'Accès modifié.'
						}
					} else {
						this.$parent.$parent.chargement = false
						this.fermerModaleAccesParcours()
						this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
					}
				}.bind(this)
				xhr.open('POST', this.$parent.$parent.hote + '/inc/modifier_acces_parcours.php', true)
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
				xhr.send('parcours=' + this.id + '&question=' + this.question + '&reponse=' + this.reponse + '&nouvellequestion=' + this.nouvellequestion + '&nouvellereponse=' + this.nouvellereponse + '&session=' + session)
			} else {
				if (this.question === '') {
					this.$parent.$parent.message = 'Veuillez sélectionner votre question secrète actuelle.'
				} else if (this.reponse === '') {
					this.$parent.$parent.message = 'Veuillez indiquer votre réponse secrète actuelle.'
				} else if (this.nouvellequestion === '') {
					this.$parent.$parent.message = 'Veuillez sélectionner une nouvelle question secrète.'
				} else if (this.nouvellereponse === '') {
					this.$parent.$parent.message = 'Veuillez indiquer une nouvelle réponse secrète.'
				}
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
				let session = ''
				if (localStorage.getItem('session')) {
					session = localStorage.getItem('session')
				}
				const xhr = new XMLHttpRequest()
				xhr.onload = function () {
					if (xhr.readyState === xhr.DONE && xhr.status === 200) {
						this.$parent.$parent.chargement = false
						this.fermerModaleConnexion()
						if (xhr.responseText === 'erreur') {
							this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
						} else if (xhr.responseText === 'non_autorise') {
							this.$parent.$parent.message = 'La question et la réponse secrètes ne sont pas correctes.'
						} else if (xhr.responseText !== '') {
							localStorage.setItem('session', xhr.responseText)
							this.admin = true
							this.$parent.$parent.notification = 'Parcours débloqué.'
						}
					} else {
						this.$parent.$parent.chargement = false
						this.fermerModaleConnexion()
						this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
					}
				}.bind(this)
				xhr.open('POST', this.$parent.$parent.hote + '/inc/ouvrir_parcours.php', true)
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
				xhr.send('parcours=' + this.id + '&question=' + this.question + '&reponse=' + this.reponse + '&session=' + session)
			} else {
				if (this.question === '') {
					this.$parent.$parent.message = 'Veuillez sélectionner une question secrète.'
				} else if (this.reponse === '') {
					this.$parent.$parent.message = 'Veuillez complétez le champ «&nbsp;Réponse secrète&nbsp;».'
				}
			}
		},
		terminerSession () {
			this.$parent.$parent.chargement = true
			let session = ''
			if (localStorage.getItem('session')) {
				session = localStorage.getItem('session')
			}
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					if (xhr.responseText === 'session_terminee') {
						this.fermerModaleParcours()
						this.admin = false
						localStorage.removeItem('session')
						this.$parent.$parent.notification = 'Session terminée.'
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleParcours()
					this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + '/inc/terminer_session_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
			xhr.send('session=' + session)
		},
		afficherSupprimerParcours () {
			this.modale = 'supprimer-parcours'
		},
		supprimerParcours () {
			this.modale = ''
			this.$parent.$parent.chargement = false
			let session = ''
			if (localStorage.getItem('session')) {
				session = localStorage.getItem('session')
			}
			const xhr = new XMLHttpRequest()
			xhr.onload = function () {
				if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					this.$parent.$parent.chargement = false
					if (xhr.responseText === 'erreur') {
						this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
					} else if (xhr.responseText === 'parcours_supprime') {
						document.title = 'Digisteps by La Digitale'
						this.$router.push('/')
					}
				} else {
					this.$parent.$parent.chargement = false
					this.fermerModaleConnexion()
					this.$parent.$parent.message = 'Erreur de communication avec le serveur.'
				}
			}.bind(this)
			xhr.open('POST', this.$parent.$parent.hote + '/inc/supprimer_parcours.php', true)
			xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
			xhr.send('parcours=' + this.id + '&session=' + session)
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
}

#blocs .bloc .texte {
	display: block;
	display: -webkit-box;
	font-size: 15px;
	margin-top: 12px;
	line-height: 1.5;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
	overflow: hidden;
	text-overflow: ellipsis;
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

#blocs .bloc .action {
	display: flex;
	justify-content: flex-end;
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

#blocs .bloc .action .bouton + .bouton {
	margin-left: 15px;
}

#blocs .bloc .action .bouton:hover {
	background: rgba(255, 255, 255, 0.75);
	text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
}

#blocs .bloc .action .bouton.travaux {
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
}

#travaux .travail {
	display: inline-block;
	width: 100%;
	padding: 10px;
	border: 1px solid #ddd;
	border-radius: 4px;
	margin-bottom: 15px;
}

#travaux .travail .meta {
	display: block;
	padding-bottom: 10px;
	margin-bottom: 10px;
	border-bottom: 1px dashed #ddd;
}

#travaux .travail .bouton {
	font-size: 11px;
	height: 30px;
	line-height: 30px;
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

#fichier .bouton + .bouton {
	margin-left: 20px;
}

#depot {
	display: flex;
	margin-bottom: 20px;
}

#depot .label {
	display: flex;
}

#depot .label:first-child {
	margin-right: 20px;
}

#depot .label input {
	margin-right: 7px;
}

#depot .label label {
	margin-bottom: 0;
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

.modale.confirmation .conteneur {
	text-align: center;
	padding: 30px 25px;
	max-width: 500px;
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
#blocs .bloc .lire {
    font-size: 12px;
    padding: 4px 10px;
    border: 1px solid;
    border-radius: 5px;
	cursor: pointer;
	margin-top: 8px;
	margin-bottom: 5px;
	user-select: none;
	transition: all .1s ease-in;
}

#blocs .bloc .lire:hover {
	background: #001d1d;
	color: #fff;
}

#blocs .bloc.section .lire {
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

	#blocs .bloc .action .bouton + .bouton {
		margin-top: 10px!important;
		margin-left: 0!important;
	}

	#blocs .bloc .action .bouton {
		font-size: 12px!important;
		width: 100%!important;
		text-align: center!important;
	}

	#blocs .bloc .action {
		flex-wrap: wrap!important;;
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
</style>
