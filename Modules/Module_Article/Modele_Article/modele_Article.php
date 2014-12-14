<?php
if ( !defined ( 'TEST_INCLUDE' ) )
    die ( "Vous n'avez pas accès directement à ce fichier" );

// BRAHIM : MAJ LE 14/12/2014 REFONTE TOTAL DU MODELE
class ModeleArticle extends DBMapper
{

    protected $_idArticle;
    protected $_reference;
    protected $_libelle;
    protected $_marque; // On peut mettre en place une table Marque et du coup, ici on stoquerai juste l'id_marque;
    // pour vérifier la validité de la marque (vue que plusieurs artilce ont la meme marque
    protected $_description;
    protected $_idCategorie;
    protected $_prix;
    protected $_quantiteStock;
    protected $_actif;
    protected $_categorie; // Alexandre

    /** Constructeur d'article
     *
     * @param $idArticle
     *
     * @throws Exception
     */
    function __construct ( $idArticle )
    {
        //ICI on récupère les informations de l'article
        $donnees = array (
                'idArticle' => $idArticle
        );
        // On met [0] car la fonction nous retourne une liste de tuples, or, ici on en a juste un seul tuple car idArticle UNIQUE
        $donneesArticle = self::requeteFromBD ( "SELECT DISTINCT * FROM article join (select idcategorie,libelle as categorie from categorie) as categorie on article.idcategorie=categorie.idcategorie WHERE idArticle = :idArticle", $donnees )[ 0 ];
        if ( $donneesArticle == NULL ) {
            throw new Exception( "L'identifiant d'article :" . $idArticle . " n'existe pas." );
        }
        $this->_idArticle     = $donneesArticle[ 'idArticle' ];
        $this->_reference     = $donneesArticle[ 'reference' ];
        $this->_libelle       = $donneesArticle[ 'libelle' ];
        $this->_marque        = $donneesArticle[ 'marque' ];
        $this->_description   = $donneesArticle[ 'description' ];
        $this->_idCategorie   = $donneesArticle[ 'idCategorie' ];
        $this->_prix          = $donneesArticle[ 'prix' ];
        $this->_quantiteStock = $donneesArticle[ 'quantiteStock' ];
        $this->_actif         = $donneesArticle[ 'actif' ];
    }

    /**     Cette méthode permet d'envoyer des requetes sur la BD
     * et de récupérer le resultat de la requete
     * Cette méthode fonctionne pour les insert ; update ; delete ; select
     *  Si vous souhaité une seul ligne, il faut récupéré l'indice 0
     * sur la valeur de retour,
     * exemple : return $resultat;
     *          $resultat[0]
     *
     * @param       $requete
     * @param array $donnees
     *
     * @return array de resultat dans le cas d'un select ou String
     * @throws Exception
     */
    private static function requeteFromBD ( $requete, $donnees = array () )
    {
        if ( $requete == NULL ) {
            throw new Exception( "Impossible de faire la requete car requete ou donnees vide" );
        }
        //Execution de la requete
        try {
            $reponse = self::$database->prepare ( $requete );
            if ( $reponse->execute ( $donnees ) == FALSE ) {
                throw new Exception ( "Requete a échoué, code erreur :" . $reponse->errorCode () . ";" );
            }
        } catch ( PDOException $e ) {
            echo 'Échec lors de la connexion : ' . $e->getMessage ();
        }
        $resultat = $reponse->fetchAll ();

        return $resultat;
    }

    /**
     * Cette fonction rajoute un dans la Base de donnée
     * avec les valeurs rentré en paramètre
     *
     * @throws Exception
     *
     * @param array (reference;libelle;marque;description;idCategorie;prix;quantiteStock;actif)
     */
    static function createArticle ( $donneesArticle )
    {
        self::verifierContenuTableau ( $donneesArticle );
        //ICI on créer l'article dans la base de donnée
        self::requeteFromBD ( "INSERT INTO article (reference, libelle, marque, description, idCategorie, prix, quantiteStock, actif) VALUES(:reference, :libelle, :marque, :description, :idCategorie, :prix, :quantiteStock, :actif)", $donneesArticle );
    }

    /** Verifie l'existances de variables puis vérifier le type
     *
     * @param $donneesArticle
     *
     * @throws Exception
     */
    static function verifierContenuTableau ( $donneesArticle )
    {
        //TODO A ameliorer pour simplifier le code
        // BRAHIM : j'ai penser à faire une boucle sur le tableau pour vérifier
        // c'est pas urgent
        if ( !isset( $donneesArticle[ 'reference' ] ) ) {
            throw new Exception( "Reference non spécifié" );
        } else {
            if ( !is_string ( $donneesArticle[ 'reference' ] ) ) {
                throw new Exception ( " La valeur de reference  est incorrecte" );
            }
        }
        if ( !isset( $donneesArticle[ 'libelle' ] ) ) {
            throw new Exception( "libelle non spécifié" );
        } else {
            if ( !is_string ( $donneesArticle[ 'libelle' ] ) ) {
                throw new Exception ( " La valeur de libelle  est incorrecte" );
            }
        }
        if ( !isset( $donneesArticle[ 'marque' ] ) ) {
            throw new Exception( "marque non spécifié" );
        } else {
            if ( !is_string ( $donneesArticle[ 'marque' ] ) ) {
                throw new Exception ( " La valeur de marque  est incorrecte" );
            }
        }
        if ( !isset( $donneesArticle[ 'description' ] ) ) {
            throw new Exception( "description non spécifié" );
        } else {
            if ( !is_string ( $donneesArticle[ 'description' ] ) ) {
                throw new Exception ( " La valeur de description  est incorrecte" );
            }
        }
        if ( !isset( $donneesArticle[ 'idCategorie' ] ) ) {
            throw new Exception( "idCategorie non spécifié" );
        } else {
            $valeur_existante = self::requeteFromBD ( "SELECT 1 from categorie where idCategorie=:idCategorie", array ( 'idCategorie' => $donneesArticle[ 'idCategorie' ] ) );
            if ( count ( $valeur_existante ) == 0 ) {
                throw new Exception( "idCategorie non reconnu" );
            }
        }
        if ( !isset( $donneesArticle[ 'prix' ] ) ) {
            throw new Exception( "prix non spécifié" );
        } else {
            if ( $donneesArticle[ 'prix' ] > 0 ) {
            } else {
                throw new Exception( "prix négatif" );
            }
        }
        if ( !isset( $donneesArticle[ 'quantiteStock' ] ) ) {
            throw new Exception( "quantiteStock non spécifié" );
        } else {
            if ( $donneesArticle[ 'quantiteStock' ] >= 0 ) {
            } else {
                throw new Exception( "quantiteStock négatif" );
            }
        }
        if ( !isset( $donneesArticle[ 'actif' ] ) ) {
            $donneesArticle[ 'actif' ] = FALSE;
        } else {
            if ( !is_bool ( $donneesArticle[ 'actif' ] ) ) {
                throw new Exception ( " La valeur pour actif est incorrecte" );
            }
        }
    }

    /* /** Modifie l'ensemble des valeurs de l'article
     * il les modifie dans la BD puis dans le php
     * @param $donneesArticle
     * @throws Exception
     */
    /** Retourne les id articles des produits qui correspondent aux conditions
     *
     * @param $conditions           :
     *                              exemple : array( 'libelle'        => "ordinateur");
     *                              retourne liste des ordinateurs
     *
     * @return array(idArticle)
     * @throws Exception
     */
    static function getArticlesConditions ( $conditions )
    {
        $elementArticle = array (
                'idArticle'     => NULL,
                'reference'     => NULL,
                'marque'        => NULL,
                'libelle'       => NULL,
                'description'   => NULL,
                'idCategorie'   => NULL,
                'prix'          => NULL,
                'quantiteStock' => NULL,
                'categorie'     => NULL,
                'actif'         => NULL
        );
        $conditions     = array_intersect_key ( $conditions, $elementArticle );
        if ( count ( $conditions ) > 0 ) {
            $conditionRequete = " where ";
        } else {
            $conditionRequete = "";
        }
        $i = 0;
        foreach ( $conditions as $key => $value ) {
            if ( $i > 0 ) {
                $conditionRequete .= " AND ";
            }
            // exemple :  marque LIKE '%APP%'
            $conditionRequete .= $key . " LIKE '%" . $value . "%' ";
            $i++;
        }
        $listeIdArticle     = self::requeteFromBD ( "SELECT idArticle from article" . $conditionRequete );
        $listeDesIdArticles = array ();
        foreach ( $listeIdArticle as $key => $value ) {
            array_push ( $listeDesIdArticles, $value[ 0 ] );
        }

        return $listeDesIdArticles;
    }

    /** Retourne l'identifiant de l'article
     * @return integer : Identifiant article
     */
    function getIdArticle ()
    {
        return $this->_idArticle;
    }

    /** Modifie l'ensemble des valeurs de l'article
     * il les modifie dans la BD puis dans le php
     * Retourne FALSE si la modification échoué;
     * Retourne VRAI si ça fonctionne
     *
     * @param $donneesArticle
     *
     * @return bool (modification reussit)
     */
    function modifierArticle ( $donneesArticle )
    {
        try {
            self::verifierContenuTableau ( $donneesArticle );
            // Le controleur est censé verifié les valeurs avant
            // Donc ici on suppose que les valeurs sont valides
            //ICI on modifie l'ensemble des valeurs de l'article dans la base de donnée
            self::requeteFromBD ( "UPDATE article SET reference = :reference, libelle = :libelle, marque = :marque, description = :description, idCategorie = :idCategorie, prix = :prix, quantiteStock = :quantiteStock, actif = :actif WHERE idArticle = :idArticle", $donneesArticle );
            // On met à jour l'objet
            $this->_reference     = $donneesArticle[ 'reference' ];
            $this->_libelle       = $donneesArticle[ 'libelle' ];
            $this->_marque        = $donneesArticle[ 'marque' ];
            $this->_description   = $donneesArticle[ 'description' ];
            $this->_idCategorie   = $donneesArticle[ 'idCategorie' ];
            $this->_prix          = $donneesArticle[ 'prix' ];
            $this->_quantiteStock = $donneesArticle[ 'quantiteStock' ];
            $this->_actif         = $donneesArticle[ 'actif' ];

            return TRUE;
        } catch ( Exception $e ) {
            return FALSE;
        }
    }

    /** Cette méthode permet de récuperer un élément de la BD
     * Il s'agit d'une sorte de mega get qui retourne l'élément qu'on veut sans besoin de
     * créer plein de fonctions get
     *
     * @param $nomElement ; exemple : "idArticle"
     *
     * @return valeur de la case voulu
     * @throws Exception
     */
    function getElement ( $nomElement )
    {
        $elementArticle = $this->getArticle ();
        if ( array_key_exists ( $nomElement, $elementArticle ) ) {
            return $elementArticle[ $nomElement ];
        } else {
            throw new Exception( "La case du tableau que vous recherchez est inconnu" );
        }
    }

    /** Retourne l'ensemble des composantes de l'article
     * @return array
     */
    function getArticle ()
    {
        return $article = array (
                'idArticle'     => $this->_idArticle,
                'reference'     => $this->_reference,
                'marque'        => $this->_marque,
                'libelle'       => $this->_libelle,
                'description'   => $this->_description,
                'idCategorie'   => $this->_idCategorie,
                'prix'          => $this->_prix,
                'quantiteStock' => $this->_quantiteStock,
                'categorie'     => $this->_categorie,
                'actif'         => $this->_actif
        );
    }

    /** Modifie la valeur de libelle
     *
     * @param $libelle
     *
     * @throws Exception
     */
    function setLibelle ( $libelle )
    {
        if ( strlen ( $libelle ) > 0 ) {
            Personnage::requeteFromBD (
                    "UPDATE article SET libelle = :libelle WHERE idArticle =:idArticle", array (
                            'idArticle' => $this->_idArticle,
                            'libelle'   => $libelle
                    ) );
            $this->_libelle = $libelle;
        } else {
            throw new Exception( 'libelle à rajouté nul ou incorrect' );
        }
    }

    /** Active l'article
     * Met la variable actif à vrai
     * @throws Exception
     */
    function activerArticle ()
    {
        // On fait toujours la requete sql avant de modifier l'objet car si il y a un bug,
        // si par exemple la requete ne fonctionne pas, la valeur ne sera pas modifier dans l'objet php (dans le cas ou ça lance une Exception)
        self::requeteFromBD ( "UPDATE article SET actif = :actif WHERE idArticle= :idArticle", array (
                'actif'     => TRUE,
                'idArticle' => $this->_idArticle
        ) );
        $this->_actif = TRUE;
    }

    /** Desactive l'article
     * Met la variable actif à faux
     * @throws Exception
     */
    function desactiverArticle ()
    {
        self::requeteFromBD ( "UPDATE article SET actif = :actif WHERE idArticle= :idArticle", array (
                'actif'     => FALSE,
                'idArticle' => $this->_idArticle
        ) );
        $this->_actif = FALSE;
    }
}
