<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for shop items
 *	- generate new amazon products from shop items
 *
 * @params
 * -
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');

$starttime = time() + microtime();
$countInsert = 0;
$countUpdate = 0;
$languageIds = getLanguageIds();

//	keep post submit
$post = $_POST;

	//	get amazon accountsites for amazon marketplaces by accountssites id
	$amazonAccountsSites = getAmazonAccountsites($post);

	//	get the shop items data object
	$data = array();
	$data['from'] = 'shop_items';
	$data['select'] = '*';
	$data['where'] = "
		active = 1
		AND exportStatus = 0
	";
	$date['order'] = "lastmod DESC";
	$shopItemsResults = SQLSelect($data['from'], $data['select'], $data['where'], $date['order'], 0, $post["limit"], 'shop',  __FILE__, __LINE__);
	if (count($shopItemsResults) > 0) 
	{
		
		//	get amazon products
		$data = array();
		$data['from'] = 'amazon_products';
		$data['select'] = 'id_product, accountsite_id, item_id, SKU, lastmod';
		$data['where'] = "
			accountsite_id = " . $amazonAccountsSites['id_accountsite'] . "
		";			
		$amazonProductsResults = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
		$amazonProductsList = array();
		$countUpdateProducts = 0;
		if (count($amazonProductsResults) > 0) 
		{
			foreach($amazonProductsResults as $amazonProduct)
			{	
				$amazonProductsList[$amazonProduct['SKU']] =  $amazonProduct;
			}
		}
			
		//	get shop items keywords
		$data = array();
		$data['from'] = 'shop_items_keywords';
		$data['select'] = 'id, GART, language_id, ordering, keyword';
		$data['where'] = "
			language_id = '" . $amazonAccountsSites['language_id'] . "'";
		$data['orderBy'] = 'ordering DESC';
		$shopItemsKeywordsResults = SQLSelect($data['from'], $data['select'], $data['where'], $data['orderBy'], 0, 0, 'shop',  __FILE__, __LINE__);	
		$shopItemsKeywordsList = array();
		if (count($shopItemsKeywordsResults) > 0) 
		{
			foreach($shopItemsKeywordsResults as $shopItemsKeyword)
			{
				$shopItemsKeywordsList[$shopItemsKeyword['GART']] =  $shopItemsKeyword;
			}
		}		
		
		foreach ($shopItemsResults as $item)
		{
			//  check if shop items lastmod higher then amazon product lastmod
            if ($item["lastmod"] > 0) 
			{	
				//	generate title with MPN
				$gart = $item['GART'];
				$success = settype($gart, "int");				
				$title = 'MAPCO ' . $item["MPN"] . ' ' . $shopItemsKeywordsList[$gart]['keyword'];

				//Get the bullet points
				$bulletPoints = updateAmazonProductsBulletPoints($item, $languageIds[$amazonAccountsSites['language_id']]);

				//	get Recommended Browse Nodes
				$recommendedBrowseNode = updateAmazonProductsBrowseNodes($item, $post['accountsite_id']);
				
				//	update or insert amazon products tabele from shop items table
				if (isset($amazonProductsList[$item['MPN']]['SKU']) && $amazonProductsList[$item['MPN']]['SKU'] == $item["MPN"]) 
				{
					$data = array();
					$data['item_id'] = $item["id_item"];
					$data['OfferingCondition'] = 'Neu';
					$data['OfferInventoryLeadTime'] = '1 Tag';
					$data['ItemPackageQuantity'] = 1;
					$data['SKU'] = $item["MPN"];
					$data['GART'] = $item["GART"];
					$data['EAN'] = $item["EAN"];	
					$data['BulletPoints'] = $bulletPoints;
					$data['RecommendedBrowseNode'] = $recommendedBrowseNode;
					$data['upload'] = 1;
					$data['submitedProduct'] = 0;
					$data['lastmod'] = time();
					$data['lastmod_user'] = getAmazonSessionUserId();
					$addWhere = "
						id_product = " . $amazonProductsList[$item['MPN']]["id_product"] . " 
						AND accountsite_id = " . $amazonAccountsSites['id_accountsite'];
					SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
					
					//	count product update
					$countUpdate++;					
				} else {

					$field = array(
						'table' => 'amazon_products',
					);
					$data = array();
					$data['item_id'] = $item["id_item"];
					$data['article_id'] = $item["article_id"];
					$data['accountsite_id'] = $amazonAccountsSites['id_accountsite'];
					$data['SKU'] = $item["MPN"];
					$data['GART'] = $item["GART"];
					$data['EAN'] = $item["EAN"];
					$data['Title'] = $title;
					$data['Description'] = getAmazonDescription($amazonAccountsSites['language_id']);
					$data['ItemPackageQuantity'] = 1;
					$data['OfferInventoryLeadTime'] = '1 Tag';
					$data['OfferingCondition'] = 'Neu';
					$data['Brand'] = $item["Brand"];
					$data['Manufacturer'] = $item["Brand"];
					$data['BulletPoints'] = $bulletPoints;
					$data['RecommendedBrowseNode'] = $recommendedBrowseNode;
					$data['upload'] = 1;
					$data['firstmod'] = time();
					$data['firstmod_user'] = getAmazonSessionUserId();
					$data['lastmod'] = time();
					$data['lastmod_user'] = getAmazonSessionUserId();	
					SQLInsert($field, $data, 'shop', _FILE__, __LINE__);

					//	count product updates
					$countInsert++;
				}

				$stoptime = time() + microtime();
				if ($stoptime-$starttime > 60)
				{
					$xmlNextCall.= '	<NextCall>' . (time() + 180) . '</NextCall>' . "\n";
					break;
				}
				
				//	save shop items ids for updating
				$updateShopItems[] = $item["id_item"];
			}
		}
		//	set shop items export status => 1
		if (sizeof($updateShopItems) > 0) 
		{	
			$data = array();
			$data['exportStatus'] = 1;
			$addWhere = "
				id_item IN (" . implode(", ", $updateShopItems) . ")
			";		
			SQLUpdate('shop_items', $data, $addWhere, 'shop', __FILE__, __LINE__);
		}		
	}
	$xml = "\n" . "<AmazonShopItemsProduct>" . "\n";
	$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
	$xml.= $xmlNextCall;
	$xml.= '	<insert>Insert Products: ' . $countInsert . '</insert>' . "\n";
	$xml.= '	<update>Update Products: ' . $countUpdate . '</update>' . "\n";
	$xml.= '</AmazonShopItemsProduct>'. "\n";
	echo $xml;

    /**
     * Returns a default mapco description
     *
     * @param null $language
     * @return string
     */
    function getAmazonDescription($language = null)
	{
		if ($language == 1) {
			$description = "MAPCO-Produkte werden in Deutschland seit 1977 angeboten und mit großem Erfolg verkauft. Millionenfach werden MAPCO-Produkte in unendlich viele Fahrzeugtypen eingebaut. Kunden-zufriedenheit besitzt stets höchste Priorität. Ursprünglich in Frankreich als Aktiengesellschaft gegründet, werden heute sämtliche MAPCO-Aktivitäten von Borkheide bei Berlin gesteuert.

			MAPCO hat sich seit mehr als 3 Jahrzehnten europaweit einen Namen als Bremsenspezialist gemacht. Obwohl das Lieferprogramm inzwischen gewaltig erweitert wurde, wird das Programm Bremsenteile innerhalb des Sortiments weiter gepflegt und entwickelt.

			MAPCO-Lenkungs und -Chassisteile werden seit 1985 auf dem deutschen Markt angeboten. Das Programm entwickelte sich allerdings erst ab etwa 1995 in die Dimension, die heute erreicht wurde. Die neuen Technologien bei der Vorder- und Hinterachskonstruktion, welche die Automobilhersteller in den 90er Jahren einführten, hat zu einem explosionsartig ansteigendem Marktpotential für diese Ersatzteile geführt. Weit mehr als 3500 Einzelpositionen werden in dieser Produktfamilie geführt. Der Produktkatalog mit Originalabbildungen ist übersichtlich und praxisnah gehalten. Qualität, Preis und Verfügbarkeit dieser Teile sind vorbildlich.

			MAPCO-Lenkgetriebe für hydraulische und mechanische Lenkungen runden das Programm ab. Auch hier hat die von der Automobilindustrie in den 90er Jahren verfolgte Politik der Erhöhung von Komfort und Sicherheit im Fahrzeug einen völlig neuen Ersatzteilmarkt entstehen lassen. Das MAPCO-Programm beinhaltet des weiteren eine Vielzahl umsatzstarker Verschleißteile.";
		}

		if ($language == 2) {
			$description = "MAPCO products have been sold with enormous success in Germany since 1977. Over the last 30 years millions of MAPCO products have been fitted to a multitude of different vehicle applications. Customer satisfaction still commands the highest priority. Originally founded as a PLC in France, the company now coordinates its entire activities as MAPCO Autotechnik GmbH from its headquarters in Borkheide, near Berlin.

			MAPCO has made itself a name in the last three decades all over Europe as a brake specialist. Although the total sales programme for other automotive replacement parts has been dramatically extended during this period, MAPCO has not neglected its original specialism and has continually developed and enhanced its range of brake parts.

			MAPCO steering and suspension parts have been available to the German market since 1985. However the programme entered its most dynamic growth phase, leading up to the impressive dimensions which have now been reached, from 1995 onwards. The new technologies for front and rear axle constructions introduced by the vehicle manufacturers in the Nineties, has lead led to an explosive growth in the market potential for these replacement parts. Far in excess of 3500 individual items are carried in this product group. The corresponding catalogue with original photos and illustrations is clearly presented and practice-oriented. The quality, price and availability of these parts set new standards in the marketplace.

			MAPCO steering racks for servo-assisted and mechanical steering round off the programme. Once again the automotive industrys sustained policy in the Nineties, aimed at increasing the comfort and safety of cars, has created a completely new replacement parts market.";
		}

		if ($language == 3) {
			$description = "";
		}

		if ($language == 4) {
			$description = "";
		}

		if ($language == 5) {
			$description = "I prodotti Mapco sono presenti sui mercati di tutto il mondo a partire dal 1977. Negli ultimi 30 anni milioni di prodotti MAPCO sono stati assemblati su ogni tipo di vettura circolante, dai modelli tedeschi a quelli francesi a quelli italiani a quelli giapponesi e coreani. Il livello di soddisfazione del cliente e´sempre stato il punto focale dell´impegno MAPCO che si tramuta in un servizio efficiente ed estremamente veloce. Originariamente fondata a Parigi, la Mapco Autotechnik Gmbh ha oggi la sua sede a Borkheide, non lontano dalla capitale Berlino.

			Il nome di MAPCO e´sempre stato associato in Germania alle parti frenanti. Nonostante cio, negli ultimi 15 anni, la gamma di prodotti si e´allargata drammaticamente fino ad includere parti tiranteria che oggigiorno rappresentano oltre la meta dell´attivita di MAPCO.

			Il programma tiranteria e´stato introdotto in Germania nel 1985. A partire dal 1995, lo sviluppo di questo importante gruppo di prodotti, ha raggiunto dimensioni tali da garantire uno sviluppo di una gamma completa che include ogni tipo di applicazione e modello di vettura attualmente presente sul mercato non solo europeo, ma mondiale. A tutt´oggi oltre 4000 articoli sono presenti nella gamma tiranteria, una gamma che si estende di circa 1,5 articoli ogni singolo giorno.

			Oltre alle parti tiranteria, altri importanti prodotti sono entrati nella famiglia Mapco e sono diventati uno standard in molti mercati internazionali. Dai giunti omocinetici (sempre con gamma completa) ai kit cuscinetto ruota e kit mozzo, ai tendicinghia, prodotti seguendo e battendo i piu alti parametri OE disponibili, agli ammortizzatori che sono stati lanciati all´inizio di questo 2010 per completare l´offerta di Mapco nel settore sospensioni.";
		}

		if ($language == 6) {
			$description = "Les produits MAPCO sont proposés en Allemagne depuis 1977 et rencontrent un grand succès. Des millions de pièces MAPCO ont déjà été montées sur une quantité innombrable de véhicules. La satisfaction de nos clients est notre constante priorité.A l’origine, MAPCO fut fondée à Paris en tant que société anonyme. Aujourd’hui, l’ensemble de nos activités est dirigé depuis nos locaux de Borkheide près de Berlin en Allemagne.

			Depuis plus de trois décennies, MAPCO a bâti sa réputation comme spécialiste du freinage. Durant l'élargissement de notre gamme, notre offre de pièces de freinage a été entretenue et développée.

			Les pièces de direction et de suspension MAPCO sont disponibles sur le marché allemand depuis 1985. A partir de 1995 cette gamme a atteint une importance capitale. Les nouvelles technologies utilisées par les constructeurs automobiles dans la construction des trains avant et arrière depuis le début des années 90 ont conduit à une croissance exponentielle du marché des pièces de liaison au sol. Plus de 3500 références constituent notre gamme pour cette famille de produit. Notre catalogue présente ces pièces de manière claire et pratique pour l’utilisateur, grâce à des photographies. La qualité, le prix et la disponibilité de ces pièces sont remarquables.

			La gamme MAPCO comporte aujourd'hui une grande variété de pièces mécaniques et électroniques. Nos gammes de roulements et moyeux de roue, de joints homocinétiques et d'amortisseurs sont en mesure de répondre à tous les besoins.";
		}

		if ($language == 7) {
			$description = "Los productos MAPCO se venden con gran éxito en Alemania desde 1977. Millones de productos Mapco se han instalado desde entonces en multitud de diferentes tipos de vehículos. Nuestra principal prioridad siempre a sido la satisfacción del cliente. Originalmente fundada en Francia como una empresa pública, en la actualidad todas las actividades de Mapco se realizan en Borkheide, localidad cercana a Berlín.

			MAPCO se ha hecho conocido en Europa durante más de 3 décadas como un especialista en frenos. Aunque la gama de productos se ha ampliado enormemente, las piezas de freno se sigue desarrollado y continua siendo un producto importante dentro de la gama.

			los repuestos MAPCO para la dirección y la suspensión están disponibles en el mercado alemán desde 1985. Este programa, sin embargo, sólo se desarrolló en la dimensión que ha alcanzado hoy en día desde aproximadamente 1995. Las nuevas tecnologías en el eje delantero y trasero, que introdujeron los fabricantes de automóviles en los años 90, dio lugar a la explosión de un mercado potencial de estas piezas de repuesto. Más de 3500 artículos individuales se suman hoy a la gama de productos. Nuestro catálogo de productos con imágenes originales se mantiene claro y práctico. La relación calidad-precio y disponibilidad de estas piezas es excepcional.

			Los engranajes de dirección de MAPCO para sistemas de dirección hidráulicos y mecánicos completan nuestra gama de productos. Aquí, también, la política seguida por la industria del automóvil en los años 90 para aumentar la comodidad y la seguridad hace que surja una oportunidad completamente nueva para el mercado de accesorios para automóvil. La gama de productos MAPCO incluye además una gran variedad de consumibles para todo tipo de vehículos.";
		}

		if ($language == 8) {
			$description = "";
		}
		return $description;
	}

