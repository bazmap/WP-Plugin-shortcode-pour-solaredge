<?php

// Espace de nom du plugin
namespace plugin_sc_solaredge;



/**
 * class-admin_page__general
 *
 * Page de paramétrages d'une page d'administration
 * Page : Général
 * Page d'administration principale
 * Paramètres créés :
 * 	cle_api
 * 	code_site
 * 	delai_peremption
 *
 * Ce fichier est appelé au sein de la classe admin
 *
 * @link       https://www.arthurbazin.com
 * @since      1.0.0
 *
 * @author     Arthur Bazin
 */



/**
 * Classe de la page d'administration : Général
 *
 * @since      1.0.0
 * @author     Arthur Bazin
 */
class admin_page__general {

	/**
	 * Objet contenant la classe parente
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      object       $parent_object
	 */
	public $parent_object;

	/**
	 * Nom de la page
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array       $page_name
	 */
	public $page_name = 'Shortcode pour SolarEdge';

	/**
	 * Nom de la page
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array       $page_name
	 */
	public $page_slug = 'sc_solaredge_general';



	/**
	 * Mise en place de l'admin
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct($init_parent_object) {

		$this->parent_object = $init_parent_object;

		$this->init_page();

		$this->init_page_content();

	}



	/**
	 * Initialisation de la page
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function init_page() {

		$page_option = add_submenu_page(
			"options-general.php",                                                   // Page parente (dans le menu)
			$this->page_name,                                                        // Titre de la page
			$this->parent_object->parent_object->get_plugin_name(),                  // Titre du menu
			'manage_options',                                                        // Privilège de l'utilisateur pour y accéder
			$this->page_slug,                                                        // Slug de la page
			array($this, 'display_page'),                                            // Fonction callback d'affichage
			10                                                                       // Priorité/position.
		);

		// Ajout d'une aide
		add_action( 'load-' . $page_option, array($this, 'display_help') );

	}



	/**
	 * Contenu de la page
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	function display_page(){
		?>
			<div class="wrap">
				<!-- Displays the title -->
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<!-- The form must point to options.php -->
				<form action="options.php" method="POST">
					<?php 
						// Output the necessary hidden fields : nonce, action, and option page name
						settings_fields( $this->parent_object->plugin_setting_group );
						// Boucle sur chaque section et champ enregistré pour cette page
						do_settings_sections( $this->page_slug );
						// Afficher le bouton de validation
						submit_button();
					?>
				</form>
			</div>
		<?php
	}



	/**
	 * Ajout d'une aide pour la page
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	function display_help() {

		// Récupération de la page courante
		$screen = get_current_screen();

		// Ajout de l'onglet d'aide
		$screen->add_help_tab(
			array(
				'id' => $this->page_slug.'_help',
				'title' => __( 'Aide' ),
				'content' => 

"<p>L'utilisation se fait simplement en plaçant le shortcode suivant : [sc_solaredge]</p>

<p>Plusieurs paramètres sont disponibles afin de spécifier les données voulues :
	<ul>
		<li>
			<code>paramètre</code> :
			<ul>
				<li>
					<code>horodatage</code> : Renvoi la date et l'heure de récupération des données.
				</li>
				<li>
					<code>lien</code> : Renvoi un lien vers les données de la commune dont le code INSEE est spécifiée dans les paramètres. A utiliser avec le paramètre : <code>texte</code>.
				</li>
				<li>
					<code>co2_eco</code> : renvoi un texte contenant la valeur de CO2 économisé.
				</li>
				<li>
					<code>so2_eco</code> : renvoi un texte contenant la valeur de SO2 économisé.
				</li>
				<li>
					<code>nox_eco</code> : renvoi un texte contenant la valeur de NOX économisé.
				</li>
				<li>
					<code>arbre_plante</code> : renvoi un texte contenant le nombre d'arbre économisé.
				</li>
				<li>
					<code>ampoule</code> : renvoi un texte contenant l'équivalent du nombre d'ampoule éteinte.
				</li>
				<li>
					<code>production_totale</code> : renvoi un texte contenant la production totale en Wh.
				</li>
				<li>
					<code>revenu_total</code> : renvoi un texte contenant le revenu total généré.
				</li>
				<li>
					<code>production_année</code> : renvoi un texte contenant la production de l'année en Wh.
				</li>
				<li>
					<code>production_mois</code> : renvoi un texte contenant la production du mois en Wh.
				</li>
				<li>
					<code>production_jour</code> : renvoi un texte contenant la production du jour en Wh.
				</li>
				<li>
					<code>production_instantannee</code> : renvoi un texte contenant la production instantanée en W.
				</li>
			</ul>
		</li>
		<li>
			<code>site</code> : permet de spécifier le code du site pour lequel récupérer les données. Par défaut il s'agt du premier site spécifié dans les paramètres.
		</li>
		<li>
			<code>texte</code> : uniquement avec le paramètre <code>indicateur=lien</code>. Texte à afficher dans le lien généré. Par défaut, le texte suivant est utilisé : \"Tableau de bord détaillé\".
		</li>
		<li>
			<code>debug</code> : utilisé sans valeur, les données bruttes sont renvoyées. Ce paramètre prime sur tous les autres.
		</li>
	</ul>
</p>

<p>Voici quelques exemples d'utilisation du shortcode :
<ul>
	<li>
		<code>[sc_solaredge]</code><br>
		=> equivalent à <code>[sc_solaredge site=\"XXXX\" parametre=\"production_instantannee\"]</code>
	</li>

	<li>
		<code>[sc_solaredge parametre=\"co2_eco\"] </code><br>
		=>equivalent à <code>[sc_solaredge site=\"XXXX\" parametre=\"co2_eco\"]</code>
	</li>

	<li>
		<code>[sc_solaredge parametre=\"production_instantannee\" debug]</code><br>
		=>equivalent à <code>[sc_solaredge debug]</code> (<code>debug</code> prime sur tout autre paramètre).
	</li>

	<li>
		<code>[sc_solaredge indicateur=\"horodatage\"]</code>
	</li>

	<li>
		<code>[sc_solaredge indicateur=\"lien\" texte=\"Données pour ma commune\"]</code>
	</li>
</ul>
</p>
"
			)
		);

		// Ajout d'une sidebar supplémentaire
		//$screen->set_help_sidebar( __( 'Hello Dolly' ) );
	}



	/**
	 * Ajout du contenu de la page
	 * Ajout des sections et des champs
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	function init_page_content(){

		// Ajout d'une section
		add_settings_section( 
			'setting_'.($this->page_slug).'_section_parametre',                 // ID de la section
			'Paramètres',                                                       // Titre
			'',                                                                 // Fonction callback
			$this->page_slug,                                                   // Slug de la page dans laquelle afficher la section
			array(
				'before_section' => '',                                        // HTML a ajouter avant la section
				'after_section' => '',                                         // HTML a ajouter après la section
				'section_class' => ''                                          // Classe de la section
			)
		);


		// Ajout d'un paramètre
		add_settings_field( 
			'setting_'.($this->page_slug).'_setting_cle_api',              // ID du champ
			'Clé d\'API',                                                  // Titre
			array($this, 'display_setting_cle_api'),                       // Fonction callback
			$this->page_slug,                                              // Page
			'setting_'.($this->page_slug).'_section_parametre',            // Section
			//array( 
			//	'label_for' => '',                                         // Id for the input and label element.
			//)
		);


		// Ajout d'un paramètre
		add_settings_field( 
			'setting_'.($this->page_slug).'_setting_code_site',          // ID du champ
			'Code des sites à récupérer',                                   // Titre
			array($this, 'display_setting_code_site'),                                 // Fonction callback
			$this->page_slug,                                              // Page
			'setting_'.($this->page_slug).'_section_parametre',            // Section
			//array( 
			//	'label_for' => '',                                         // Id for the input and label element.
			//)
		);


		// Ajout d'un paramètre
		add_settings_field( 
			'setting_'.($this->page_slug).'_setting_delai_peremption',          // ID du champ
			'Délais de péremption des données (en seconde)',                                   // Titre
			array($this, 'display_setting_delai_peremption'),                                 // Fonction callback
			$this->page_slug,                                              // Page
			'setting_'.($this->page_slug).'_section_parametre',            // Section
			//array( 
			//	'label_for' => '',                                         // Id for the input and label element.
			//)
		);

	}


	/**
	 * Affichage d'un champ 
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @param    array  $args    Arguments récupérer depuis l'appel dans la fonction add_settings_field()
	*/
	public function display_setting_cle_api( $args ){

		$setting_name = $this->parent_object->plugin_setting_name;
		$sub_setting_name = 'cle_api';

		$setting = get_option( $setting_name );
		$value = ! empty( $setting[$sub_setting_name] ) ? $setting[$sub_setting_name] : '';
		$label = ! empty( $args['label_for'] ) ? $args['label_for'] : '';

		?>
			<input id="<?php echo esc_attr( $label ); ?>" class="regular-text" type="text" name="<?php echo esc_attr( $setting_name.'['.$sub_setting_name.']' ); ?>" value="<?php echo esc_attr( $value ); ?>"><br/>
			<p>La clé d'API peut être récupérée sur le site d'Atmo Auvergne Rhône Alpes : <a href="http://api.atmo-aura.fr/documentation" target="_blank">https://api.atmo-aura.fr/documentation</a></p>
		<?php
	}


	/**
	 * Affichage d'un champ 
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @param    array  $args    Arguments récupérer depuis l'appel dans la fonction add_settings_field()
	*/
	public function display_setting_code_site( $args ){

		$setting_name = $this->parent_object->plugin_setting_name;
		$sub_setting_name = 'code_site';

		$setting = get_option( $setting_name );
		$value = ! empty( $setting[$sub_setting_name] ) ? $setting[$sub_setting_name] : '';
		$label = ! empty( $args['label_for'] ) ? $args['label_for'] : '';

		?>
			<input id="<?php echo esc_attr( $label ); ?>" class="regular-text" type="text" name="<?php echo esc_attr( $setting_name.'['.$sub_setting_name.']' ); ?>" value="<?php echo esc_attr( $value ); ?>"><br/>
			<p>Il est possible de spécifier plusieurs sites en les séparant par des virgules : <code>1,2,3</code></p>
		<?php
	}


	/**
	 * Affichage d'un champ 
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @param    array  $args    Arguments récupérer depuis l'appel dans la fonction add_settings_field()
	*/
	public function display_setting_delai_peremption( $args ){

		$setting_name = $this->parent_object->plugin_setting_name;
		$sub_setting_name = 'delai_peremption';

		$setting = get_option( $setting_name );
		$value = ! empty( $setting[$sub_setting_name] ) ? $setting[$sub_setting_name] : '';
		$label = ! empty( $args['label_for'] ) ? $args['label_for'] : '';

		?>
			<input id="<?php echo esc_attr( $label ); ?>" class="regular-text" type="number" name="<?php echo esc_attr( $setting_name.'['.$sub_setting_name.']' ); ?>" value="<?php echo esc_attr( $value ); ?>"><br/>
			<p>Le délais de péremption est par défaut de 3h (10800 secondes). Au delà, l'API est recontactée pour actualiser les données.</p>
		<?php
	}


}