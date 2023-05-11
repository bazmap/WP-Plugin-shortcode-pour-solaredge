<?php

// Espace de nom du plugin
namespace plugin_sc_solaredge;



/**
 * class-functions
 *
 * Fichier contenant la classe function
 *
 * @link       https://www.arthurbazin.com
 * @since      1.0.0
 *
 * @author     Arthur Bazin
 */



/**
 * Classe de fonctionnalités du plugin
 *
 * Cette classe apporte les fonctions du plugin
 *
 * @since      1.0.0
 * @author     Arthur Bazin
 */
class functionalities {

	/**
	 * Objet contenant la classe parente
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      object       $parent_object
	 */
	public $parent_object;

	/**
	 * Nom de l'option qui stocke les données
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string       $option_name
	 */
	public $option_name;

	/**
	 * Temps en seconde de préemption des données stockées (avant appel de l'API)
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      integer       $delai_peremption
	 */
	public $delai_peremption;

	/**
	 * Clé d'api
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string       $api_key
	 */
	public $api_key;

	/**
	 * Code site
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string       $code_site
	 */
	public $code_site;



	/**
	 * Mise en place des fonctionnalités
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct($init_parent_object) {

		// Définition des variables
		$this->parent_object = $init_parent_object;
		$this->option_name = ($this->parent_object->get_plugin_abrv()).'_data';


		// Récupération des paramètres du plugin
		$this->api_key = 
			isset(
				(get_option($this->parent_object->plugin_object_admin->plugin_setting_name))['cle_api']
			) ?
			(get_option($this->parent_object->plugin_object_admin->plugin_setting_name))['cle_api'] :
			''
		;
		$this->code_site = 
			isset(
				(get_option($this->parent_object->plugin_object_admin->plugin_setting_name))['code_site']
			) ?
			(get_option($this->parent_object->plugin_object_admin->plugin_setting_name))['code_site'] :
			''
		;
		$this->delai_peremption = 
			isset(
				(get_option($this->parent_object->plugin_object_admin->plugin_setting_name))['delai_peremption']
			) ?
			(get_option($this->parent_object->plugin_object_admin->plugin_setting_name))['delai_peremption'] :
			( 3 * 3600 )
		; 	// 3 heures par défaut


		// Ajout d'un shortcode dédié
		add_shortcode( 'sc_solaredge', array( $this, 'shortcode_function' ) );

	}



	/**
	 * Shortcode d'affichage des données
	 *
	 * @since     1.0.0
	 * @param     array     $atts         Attributs
	 * @param     string    $content      Contenu
	 * @param     string    $shortcode    Nom du shortcode
	 * @return    string    La données affichée
	 */
	public function shortcode_function($atts, $content, $shortcode){


		// Récupération des attributs
		$attribut = shortcode_atts(
			array(
				'site' => (explode(",", $this->code_site))[0],
				'parametre' => 'production_instantannee',
				'texte' => 'Tableau de bord détaillé'
			), 
			$atts
		);


		// Récupération des données 
		$data = $this->get_data(
			$this->option_name,
			$this->delai_peremption,
			$this->api_key,
			$this->code_site
		);



		// Utilisation d'un try pour éviter les erreurs PHP
		try {

			// Récupération de la donnée
			if ( 
				gettype($atts) == 'array'
				and (
					in_array('debug', $atts) 
					or array_key_exists('debug', $atts)
				)
			){
				$data_return = $data;
			}
			else if ( $attribut['parametre'] == 'horodatage' ){

				$date = new \DateTime();
				$date->setTimestamp($data['timestamp_data']);
				$date->setTimezone(new \DateTimeZone(wp_timezone_string()));

				$data_return = $date->format("Y-m-d H:i:s");
			}
			else if ( $attribut['parametre'] == 'lien' ){
				$data_return = '<a href="https://monitoringpublic.solaredge.com/solaredge-web/p/site/'.$attribut['site'].'#/dashboard" target="_blank">'.$attribut['texte'].'</a>';
			}
			else {
				$data_return = $data['data'][$attribut['site']][$attribut['parametre']];
			}

		} 

		catch (\Throwable $th) {

			$data_return = 'Les paramètres indiqués sont incorrects';

		}

		finally {

			// Formatage éventuel de la donnée
			if (is_array($data_return)) {
				return json_encode($data_return);
			}
			else {
				return $data_return;
			}

		}

	}



	/**
	 * Récupération des données à afficher
	 *
	 * @since     1.0.0
	 * @param     string    $option_name          Nom de l'option qui stocke les données
	 * @param     string    $delai_peremption     Temps en seconde de préemption des données stockées (avant appel de l'API)
	 * @param     string    $api_key              Clé d'api
	 * @param     string    $code_insee           Code INSEE de la commune
	 * @return    array     Un tableau de données
	 */
	public function get_data($option_name, $delai_peremption, $api_key, $code_site){

		// Récupération des données stockées
		$data = get_option($option_name);

		$current_timestamp = new \DateTime('now', new \DateTimeZone('UTC'));


		// Calcul de l'interval depuis la dernière récupération des données
		if( $data['timestamp_data'] ){

			$timestamp_interval = $current_timestamp->getTimestamp() - $data['timestamp_data'];

		}
		else {

			$timestamp_interval = $delai_peremption;

		}


		// Si les données sont périmées
		if( $timestamp_interval >= $delai_peremption ) {

			// Récupération données d'API
			$solar_data = $this->get_api_data($api_key, $code_site);

			// Réorganisation des données
			foreach ($solar_data as $site => $site_data){
				$data['data'][$site]['co2_eco'] = round($site_data['environnement']['envBenefits']['gasEmissionSaved']['co2'], 1).' kg';
				$data['data'][$site]['so2_eco'] = round($site_data['environnement']['envBenefits']['gasEmissionSaved']['so2'], 1).' kg';
				$data['data'][$site]['nox_eco'] = round($site_data['environnement']['envBenefits']['gasEmissionSaved']['nox'], 1).' kg';
				$data['data'][$site]['arbre_plante'] = round($site_data['environnement']['envBenefits']['treesPlanted'], 2);
				$data['data'][$site]['ampoule'] = round($site_data['environnement']['envBenefits']['lightBulbs'], 0);

				$data['data'][$site]['production_totale'] = $this->add_power_prefix($site_data['overview']['overview']['lifeTimeData']['energy']).'Wh';
				$data['data'][$site]['revenu_total'] = round($site_data['overview']['overview']['lifeTimeData']['revenue'], 2).'€';
				$data['data'][$site]['production_annee'] = $this->add_power_prefix($site_data['overview']['overview']['lastYearData']['energy']).'Wh';
				$data['data'][$site]['production_mois'] = $this->add_power_prefix($site_data['overview']['overview']['lastMonthData']['energy']).'Wh';
				$data['data'][$site]['production_jour'] = $this->add_power_prefix($site_data['overview']['overview']['lastDayData']['energy']).'Wh';
				$data['data'][$site]['production_instantannee'] = $this->add_power_prefix($site_data['overview']['overview']['currentPower']['power']).'W';
			}

			// Assignation d'un timestamp
			$data['timestamp_data'] = $current_timestamp->getTimestamp();

			// Sauvegarde des données	
			update_option($option_name, $data);

		}

		return $data;

	}



	/**
	 * Récupération des données issues de l'API Atmo AuRA
	 *
	 * @since     1.0.0
	 * @param     string    $api_key        Clé d'api
	 * @param     string    $code_insee     Code INSEE de la commune
	 * @return    array     Un tableau de données par api appelée
	 */
	public function get_api_data($api_key, $codes_sites){

		$data = array();

		foreach (explode(",", $codes_sites) as $key => $code_site){

			// APIs
			$api = [
				'overview' => [
					'url' => " https://monitoringapi.solaredge.com/site/{$code_site}/overview?api_key={$api_key}",
					'parameter' => [
						'method' => 'GET',
						'timeout' => 30
					]
				],
				'environnement' => [
					'url' => "https://monitoringapi.solaredge.com/site/{$code_site}/envBenefits?api_key={$api_key}&systemUnits=Metrics",
					'parameter' => [
						'method' => 'GET',
						'timeout' => 30
					]
				]
			];

			// Récupération des données
			$response = array();

			foreach ($api as $key => $value){
				$response[$key] = wp_remote_get($value['url'], $value['parameter']);

				// Traitement de la réponse
				if(
					!is_wp_error($response[$key]) 
					&& (
						$response[$key]['response']['code'] == 200 
						|| $response[$key]['response']['code'] == 201
					)
				) {
					$data[$code_site][$key] = array();

					$data[$code_site][$key] = json_decode($response[$key]['body'], TRUE);

				}
				else {
					$data[$code_site][$key] = ($response[$key]);
				}
			}
		}
		
		return $data;
	}



	/**
	 * Calcul d'un préfixe dans le système d'unité international
	 *
	 * @since     1.0.0
	 * @param     string    $number        Valeur numérique à traiter
	 * @return    string    Valeur arrondie avec le préfixe correspondant
	 */


	// Fonction de traitement des puissances de 10
	public function add_power_prefix($number, $separateur = ' '){

		$result = array();

		$power_list = 
			[
				[
					'puissance' => 27, 
					'nom' => 'quadrilliard', 
					'prefixe' => 'ronna-', 
					'symbole' => 'R'
				],
				[
					'puissance' => 24, 
					'nom' => 'quadrillion', 
					'prefixe' => 'yotta-', 
					'symbole' => 'Y'
				],
				[
					'puissance' => 21, 
					'nom' => 'trilliard', 
					'prefixe' => 'zetta-', 
					'symbole' => 'Z'
				],
				[
					'puissance' => 18, 
					'nom' => 'trillion', 
					'prefixe' => 'exa-', 
					'symbole' => 'E'
				],
				[
					'puissance' => 15, 
					'nom' => 'billiard', 
					'prefixe' => 'péta-', 
					'symbole' => 'P'
				],
				[
					'puissance' => 12, 
					'nom' => 'billion', 
					'prefixe' => 'téra-', 
					'symbole' => 'T'
				],
				[
					'puissance' => 9, 
					'nom' => 'milliard', 
					'prefixe' => 'giga-', 
					'symbole' => 'G'
				],
				[
					'puissance' => 6, 
					'nom' => 'million', 
					'prefixe' => 'méga-', 
					'symbole' => 'M'
				],
				[
					'puissance' => 3, 
					'nom' => 'mille', 
					'prefixe' => 'kilo-', 
					'symbole' => 'k'
				],
				[
					'puissance' => 0, 
					'nom' => 'un', 
					'prefixe' => '', 
					'symbole' => ''
				],
				[
					'puissance' => -3, 
					'nom' => 'millième', 
					'prefixe' => 'milli-', 
					'symbole' => 'm'
				],
				[
					'puissance' => -6, 
					'nom' => 'millionième', 
					'prefixe' => 'micro-', 
					'symbole' => 'μ'
				],
				[
					'puissance' => -9, 
					'nom' => 'milliardième', 
					'prefixe' => 'nano-', 
					'symbole' => 'n'
				],
				[
					'puissance' => -12, 
					'nom' => 'billionième', 
					'prefixe' => 'pico-', 
					'symbole' => 'p'
				],
				[
					'puissance' => -15, 
					'nom' => 'billiardième', 
					'prefixe' => 'femto-', 
					'symbole' => 'f'
				],
				[
					'puissance' => -18, 
					'nom' => 'trillionième', 
					'prefixe' => 'atto-', 
					'symbole' => 'a'
				],
				[
					'puissance' => -21, 
					'nom' => 'trilliardième', 
					'prefixe' => 'zepto-', 
					'symbole' => 'z'
				],
				[
					'puissance' => -24,
					'nom' => 'quadrillionième', 
					'prefixe' => 'yocto-', 
					'symbole' => 'y'
				]
			]
		;


		// Si la valeur n'est pas numérique, on ne calcul rien
		if ( !is_numeric($number) ) {
			return $number;
		}


		// Si la valeur = 0
		if ($number == 0 ) {

			$result['value'] = round($number, 2);
			$result['prefixe'] = '';

		}

		// Sinon
		else {

			usort($power_list, function($a, $b) {
				return $a['puissance'] <=> $b['puissance'];
			});

			foreach ($power_list as $power_element) {

				if ( $number < pow(10, ($power_element['puissance'] + 3)) ){

					$result['value'] = round($number / pow(10, $power_element['puissance']), 2);

					$result['prefixe'] = $power_element['symbole'];

					break;
				}

			}

		}

		return $result['value'] . $separateur . $result['prefixe'];

	}


}