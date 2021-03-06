<?php

namespace techdeals;
if(file_exists('env.php')) {
	require('env.php'); 
}

class Database {
	public static $instance_bdd = null;
	private $bdd;

	public function __construct( $login, $password, $database_name, $host = 'localhost' ) {
		try {
			$this->bdd = new \PDO( "mysql:dbname=$database_name;host=$host", $login, $password );
		} catch ( \PDOException $e ) {
			echo 'Connexion échouée : ' . $e->getMessage();
		}
	}

	public static function getDatabase() {
		if ( ! self::$instance_bdd ) {
			self::$instance_bdd = new Database( getenv('DB_LOGIN'), getenv('DB_PASS'), getenv('DB_NAME'), getenv('DB_SERVER') );
		}

		return self::$instance_bdd;
	}

	public function lastInsertId() {
		return $this->bdd->lastInsertId();
	}

	public function setAttribute( $attribut, $value ) {
		$this->bdd->setAttribute( $attribut, $value );
	}

	public function getUsers() {
		switch ( Session::getInstance()->doubleRead( 'connected', 'status' ) ) {
			case 'SUPER_ADMIN':
				$users = $this->query( 'SELECT `id_user`, `last_name`, `first_name`, `username`, `email`, `password`, `img_user_profile`, `status`, `created_at`, `last_connection` FROM `users`' )->fetchAll();
				break;
			case 'ADMIN':
				$users = $this->query( 'SELECT `id_user`, `last_name`, `first_name`, `username`, `email`, `password`, `img_user_profile`, `status`, `created_at`, `last_connection` FROM `users` WHERE status  NOT IN (:val1,:val2)', [
					':val1' => 'SUPER_ADMIN',
					':val2' => 'ADMIN',
				] )->fetchAll();
				break;
			default:
				$users = $this->query( 'SELECT `id_user`, `last_name`, `first_name`, `username`, `email`, `password`, `img_user_profile`, `status`, `created_at`, `last_connection` FROM `users` WHERE id_user = :val', [
					':val' => Session::getInstance()->doubleRead( 'connected', 'id_user' ),
				] )->fetchAll();
				break;
		}

		foreach ( $users as list( $id_user, $last_name, $first_name, $username, $email, $password, $img_usr_profile, $status, $created_at, $last_connection ) ) {
			echo '<tr id="' . $id_user . '">
                            <td name="id_user">' . $id_user . '</td>
                            <td name="last_name" class="possible">' . $last_name . '</td>
                            <td name="first_name" class="possible">' . $first_name . '</td>
                            <td name="username" class="possible">' . $username . '</td>
                            <td name="email" class="possible">' . $email . '</td>
                            <td name="password">' . preg_replace( '/./', '*', $password ) . '</td>
                            <td name="img_user_profile" class="possible">' . $img_usr_profile . '</td>
                            <td name="status" class="possible select">' . $status . '</td>
                            <td>' . $created_at . '</td>
                            <td>' . $last_connection . '</td>
                            <td>
                            <span id="' . $id_user . '">
                            <a id="modify-' . $id_user . '" class="modify btn-primary" href="" title="Modifier"><i class="fa fa-fw fa-pencil" aria-hidden="true"></i></a>
                            <a id="cancel-' . $id_user . '" class="cancelMod btn-danger" href="" title="Annuler" hidden="true"><i class="fa fa-fw fa-close" aria-hidden="true"></i></a>
                            </span>
                            <a id="repare" class="btn-warning" href="?id=' . $id_user . '&action=2" title="Réparer"><i class="fa fa-fw fa-wrench" aria-hidden="true"></i></a>
                            <a id="delete" class="btn-danger" href="?id=' . $id_user . '&action=3" title="Supprimer"><i class="fa fa-fw fa-trash" aria-hidden="true"></i></a>
                            </td>
                        </tr>';
		}
	}

	/**
	 * @param $query
	 * @param bool|array $params
	 *
	 * @return \PDOStatement
	 */
	public function query( $query, $params = false ) {
		if ( $params ) {
			$req = $this->bdd->prepare( $query );
			$req->execute( $params ) or die( print_r( $req->errorInfo() ) );
		} else {
			$req = $this->bdd->query( $query ) or die( var_dump( $this->bdd->errorInfo() ) );
		}

		return $req;
	}

	public function getProducts() {
		switch ( Session::getInstance()->doubleRead( 'connected', 'status' ) ) {
			case 'SUPER_ADMIN':
			case 'ADMIN':
				$products = $this->query( 'SELECT `id_product`, `name_product`, `price_product`, `specs_product`, `desc_product`, `img_product`, `rank_product`, `name_category`, `quantity_product`, `is_hidden`, `username`, `published_at_product`, `last_modification_product` FROM `products` LEFT JOIN `category_` ON products.id_category = category_.id_category LEFT JOIN `users` ON  products.id_user = users.id_user' )->fetchAll();
				$edit     = 'class="possible select"';
				break;
			case 'PARTNER':
				$products = $this->query( 'SELECT `id_product`, `name_product`, `price_product`, `specs_product`, `desc_product`, `img_product`, `rank_product`, `name_category`, `quantity_product`, `is_hidden`, `username`, `published_at_product`, `last_modification_product` FROM `products` LEFT JOIN `category_` ON products.id_category = category_.id_category LEFT JOIN `users` ON  products.id_user = users.id_user WHERE username = :curr_username', [
					':curr_username' => Session::getInstance()->doubleRead( 'connected', 'username' ),
				] )->fetchAll();
				$edit     = '';
				break;
			default:
				return;
		}

		foreach ( $products as list( $id_product, $name_product, $price_product, $specs_product, $desc_product, $img_product, $rank_product, $category_product, $quantity_product, $is_hidden, $id_user, $published_at_product, $last_mod_product ) ) {
			echo '<tr id="' . $id_product . '">
                            <td name="id_product">' . $id_product . '</td>
                            <td name="name_product" class="possible">' . $name_product . '</td>
                            <td name="price_product" class="possible">' . $price_product . '</td>
                            <td name="specs_product" class="possible">' . $specs_product . '</td>
                            <td name="desc_product" class="possible">' . $desc_product . '</td>
                            <td name="img_product" class="possible">' . $img_product . '</td>
                            <td name="rank_product" class="possible">' . $rank_product . '</td>
                            <td name="id_category" class="possible select">' . $category_product . '</td>
                            <td name="quantity_product" class="possible">' . $quantity_product . '</td>
                            <td name="is_hidden" class="possible">' . $is_hidden . '</td>
                            <td name="id_user" '. $edit .'>' . $id_user . '</td>
                            <td>' . $published_at_product . '</td>
                            <td>' . $last_mod_product . '</td>
                            <td>
                            <span id="' . $id_product . '">
                            <a id="modify-' . $id_product . '" class="modify btn-primary" href="" title="Modifier"><i class="fa fa-fw fa-pencil" aria-hidden="true"></i></a>
                            <a id="cancel-' . $id_product . '" class="cancelMod btn-danger" href="" title="Annuler" hidden="true"><i class="fa fa-fw fa-close" aria-hidden="true"></i></a>
                            </span>
                            <a id="delete" class="btn-danger" href="?id=' . $id_product . '&action=3" title="Supprimer"><i class="fa fa-fw fa-trash" aria-hidden="true"></i></a>
                            </td>
                        </tr>';
		}
	}

	public function getCategory() {
		switch ( Session::getInstance()->doubleRead( 'connected', 'status' ) ) {
			case 'SUPER_ADMIN':
			case 'ADMIN':
				$category = $this->query( 'SELECT `id_category`, `name_category`, `id_parent_cat`, `published_at_category`, `last_modification_category` FROM `category_`' )->fetchAll();
				break;
			default:
				return;
				break;
		}

		foreach ( $category as list( $id_category, $name_category, $id_parent_cat, $published_at_category, $last_mod_category ) ) {
            $parent_category = $this->query('SELECT name_category FROM category_ WHERE id_category = :id_c', [
                ':id_c' => $id_parent_cat,
            ])->fetch(\PDO::FETCH_COLUMN);
		    echo '<tr id="' . $id_category . '">
                            <td name="id_category">' . $id_category . '</td>
                            <td name="name_category" class="possible">' . $name_category . '</td>
                            <td name="id_parent_cat" class="possible select">' . $parent_category . '</td>
                            <td>' . $published_at_category . '</td>
                            <td>' . $last_mod_category . '</td>
                            <td>
                            <span id="' . $id_category . '">
                            <a id="modify-' . $id_category . '" class="modify btn-primary" href="" title="Modifier"><i class="fa fa-fw fa-pencil" aria-hidden="true"></i></a>
                            <a id="cancel-' . $id_category . '" class="cancelMod btn-danger" href="" title="Annuler" hidden="true"><i class="fa fa-fw fa-close" aria-hidden="true"></i></a>
                            </span>
                            <a id="repare" class="btn-danger" href="?id=' . $id_category . '&action=3" title="Supprimer"><i class="fa fa-fw fa-trash" aria-hidden="true"></i></a>
                            </td>
                        </tr>';
		}
	}
}
