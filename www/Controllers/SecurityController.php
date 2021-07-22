<?php

namespace App\Controller;

use App\Core\Helpers;

use App\Core\View;
use App\Core\Security as Secu;
use App\Core\FormValidator;

use App\Core\Mailing;

use App\Models\User;

class SecurityController
{

	# view all users for admin only
	public function getAllUsersAction(){

        $user = Secu::getConnectedUser();
		if(is_null($user)) header("Location:/lbly-admin/login");


		if($user->isAdmin()){
			$view = new View("admin","back");

			$users = $user->all();

			$view->assign("users", $users);

		}else{
			header("HTTP/1.0 403 Forbidden");
		 	$view = new View('403');
		}



	}

	/*	
	*	superadmin can modify all users status
	*	admin can modify all users status except admin
	*/
	public function modifyRoleAction(){

		$user = Secu::getConnectedUser();
		if(is_null($user)) header("Location:/lbly-admin/login");

		if($user->isAdmin()){

			if(isset($_GET['userid'])){

				$userModified = new User();
				if($userModified->verifyId($_GET['userid']) == 1){

					$userModified->setAllFromId($_GET['userid']);

					$view = new View("changeRole", "back");
					$form = $userModified->formRoles();

					$infos = [];

					if(!$userModified->isValidated()){

						$infos[0] =  "Un Administrateur doit valider cet utilisateur</br>";
						$view->assign("infos", $infos);

					}else{

						if($userModified->isAdmin()){
							isset($infos[0])? $infos[0].= "Administrateur</br>":$infos[0] = "Administrateur</br>";
							$view->assign("infos", $infos);
						}
						if($userModified->isContributor()){
							isset($infos[0])? $infos[0].= " Contributeur</br>":$infos[0] = "Contributeur</br>";
							$view->assign("infos", $infos);

						}
						if($userModified->isAuthor()){
							isset($infos[0])? $infos[0].= " Auteur</br>":$infos[0] = "Auteur</br>";

							$view->assign("infos", $infos);

						}
						if($userModified->isEditor()){
							isset($infos[0])? $infos[0].= " Editeur</br>":$infos[0] = "Editeur</br>";

							$view->assign("infos", $infos);

						}
					}


					if(!empty($_POST)){
						$infos = [];
					}					


					if(isset($_POST['admin'])){
						$userModified->addStatus(USERADMIN);
						$infos[] = $userModified->getEmail()." est devenu Administrateur.";

					}else if(isset($_POST['valider'])){
						if($userModified->isAdmin()){
							$infos[] = $userModified->getEmail()." n'est plus Administrateur.";
						}
						$userModified->unflagStatus(USERADMIN);

					}

					if(isset($_POST['contributor'])){
						$userModified->addStatus(USERCONTRIBUTOR);
						$infos[] = $userModified->getEmail()." est devenu Contributeur.";

					}else if(isset($_POST['valider'])){
						if($userModified->isAuthor()){
							$infos[] = $userModified->getEmail()." n'est plus Contributeur.";
						}
						$userModified->unflagStatus(USERCONTRIBUTOR);

					}

					if(isset($_POST['author'])){
						$userModified->addStatus(USERAUTHOR);
						$infos[] = $userModified->getEmail()." est devenu Auteur.";


					}else if(isset($_POST['valider'])){
						if($userModified->isAuthor()){
							$infos[] = $userModified->getEmail()." n'est plus Auteur.";

						}
							$userModified->unflagStatus(USERAUTHOR);

					}

					if(isset($_POST['editor'])){

						$infos[] = $userModified->getEmail()." est devenu Editeur.";
						$userModified->addStatus(USEREDITOR);

					}else if(isset($_POST['valider'])){
						if($userModified->isEditor()){
							$infos[] = $userModified->getEmail()." n'est plus Editeur.";
						}
						$userModified->unflagStatus(USEREDITOR);

					}

					if(isset($_POST['validated'])){
						$userModified->addStatus(USERVALIDATED);
						$infos[] = $userModified->getEmail()." a été validé.";


					}else if(isset($_POST['valider'])){
						if($userModified->isValidated()){
							$infos[] = $userModified->getEmail()." a été invalidé.";
						}


						$userModified->unflagStatus(USERVALIDATED);

					}

					if(isset($infos)){

						$view->assign("infos", $infos);
						#header("Refresh:5; url=/lbly-admin/adminview", true, 303);

					}

					$userModified->save();

				}else{

					$view->assign("errors", ["Id inexistant.</br>Redirection..."]);
					header("Refresh:3; url=/", true, 303);
				}


			}else{
				header("Location: /");
			}

		}else{
			header("HTTP/1.0 403 Forbidden");
		 	$view = new View('403');

		}

					$view->assign("form",$form);

	}

	/*	delete user form asking for the user pwd or the admin pwd	*/
	public function deleteUserAction(){

		$user = Secu::getConnectedUser();
		if(is_null($user)) header("Location:/lbly-admin/login");

		$view = new View("deleteuser", "back");

		$form = $user->formDelete(); # confirm pwd of the user or the admin

		$userDelete = new User();

		$self = false; # not self delete

		# admin can delete a user 
		if(isset($_GET['userid']) && $user->isAdmin()){

			if($_GET['userid'] == $user->getId())
				$self = true;
			
			if($userDelete->verifyId($_GET['userid']) == 1){
				$userDelete->setAllFromId($_GET['userid']);

			}else{
				# id does not exist
				$view->assign("errors", ["Id inexistant.</br>Redirection..."]);
				header("Refresh:3; url=/", true, 303);

			}


		}else if(isset($_GET['userid']) && !$user->isAdmin()){
			header("Location: /");

		# user delete himself
		}else{ 
			$userDelete = $user;
			$self = true;
		}

		if($userDelete->isDeleted()){
			header("Location: /lbly-admin/adminview");
		}

		if(isset($_POST['pwdConfirm'])){

			if(password_verify($_POST['pwdConfirm'], $user->getPwd())){
				$userDelete->addStatus(USERDELETED);
				$userDelete->save();
				$view->assign("infos", ["Le compte ".$userDelete->getEmail()." a bien été supprimé.</br>Vous allez être redirigé."]);

				if($self)
					header("Refresh:4; url=/lbly-admin/logout", true, 303); 
				else
					header("Refresh:4; url=/lbly-admin/adminview", true, 303);


			}else{
				$view->assign("errors", ["Le mot de passe est erroné"]);

			}
			

		}

			
		$view->assign("form", $form);


	}

	public function forgetPwdAction(){

		$user = new User();

		$view = new View("forgetpwd");

		$form = $user->formForgetPwd();


		if(!empty($_POST['email'])){

			$email = $_POST['email'];

			$mailExists = $user->verifyMail($email); # verify exists and unicity in database

			if($mailExists == 1){ # mail found in db

				$user->setAllFromEmail($email);

				if($user->isValidated()){ # only superadmin validates

					# send mail with link to change pwd
					$mailing = Mailing::getMailing();
					$mailing->mailForgetPwd($user);
					$mailing->setRecipient($email);
					$mailing->sendMail();
					$infos = htmlspecialchars("Un lien vous a été envoyé par mail pour changer votre mot de passe.");
					$view->assign("infos", [$infos]);

				}else{
					$view->assign("infos", ["Votre compte n'a pas été validé."]);
				}


			}else{
				$view->assign("infos", ["Ce mail n'est pas inscrit."]);

			}

		}

		$view->assign("form", $form);

	}

	# action from link by mail to reset pwd user account
	public function resetPwdAction(){

		if(is_null($_GET['id']) || is_null($_GET['token']))
			header("Location: /");
		
		$id = $_GET['id'];
		$token = $_GET['token'];

		$user = new User();

		$view = new View("resetpwd");

		$form = $user->formResetPwd();
		
		if($user->verifyUser($id,$token) == 1){ # check user in db with its id and token couple

			$user->setAllFromId($id);

			$user->setToken(Helpers::createToken());

			if(!empty($_POST['pwd']) && !empty($_POST['pwdConfirm'])){

				$errors = FormValidator::check($form, $_POST);

				if(empty($errors)){

					if ($_POST['pwd'] == $_POST['pwdConfirm']) {

						$pwd = password_hash($_POST['pwd'], PASSWORD_DEFAULT);
						$user->setPwd($pwd);

						$user->save();

						header("Location: /lbly-admin/login");


					}else{
						$view->assign("errors", ["Vos mots de passe sont différents."]);

					}

				}else{
					$view->assign("errors",$errors);
				}

			}

		}else{
			header("Location: /"); # ID AND TOKEN NOT FOUND IN DB
		}

		$view->assign("form",$form);

	}


	# action from link sent by mail to confirm user account
	public function userValidatedAction(){

		if(is_null($_GET['id']) || is_null($_GET['token']))
			header("Location: /");

		$id = $_GET['id'];
		$token = $_GET['token'];

		$user = new User();
		
		if($user->verifyUser($id,$token) == 1){ # check user in db with this id and token couple

			$user->setAllFromId($id);
			$user->addStatus(USERVALIDATED);

			$user->setToken(Helpers::createToken());

			$user->save();

			header("Location:/lbly-admin"); # temporairement

		}else{
			echo "ERREUR VERIFICATION ID ET TOKEN !";
		}

	}

	public function editProfilAction(){

		session_start();

		if (!isset($_SESSION['id'])) header("Location:/"); # si user non connecté => redirection

		$user = new User();
		$user->setAllFromId($_SESSION['id']); # recuperer objet depuis session
		#var_dump($user);
		# CHERCHER LES INFOS USER EN BASE A PARTIR DE SON ID
		# A PARTIR DE SON EMAIL UNIQUE A CHACUN CEST BON AUSSI JPENSE

		$view = new View("editProfil", 'back'); # appelle View/editProfil.view.php

		$form = $user->formEditProfil(); # recupere les config et inputs de ce formulaire

		if (!empty($_POST)) {

			if (!empty($_POST['oldpwd'])) { # il faut le mot de passe pour valider tout changement

				if (password_verify($_POST['oldpwd'], $user->getPwd())) {

					if ($_POST['firstname'] != $user->getFirstname()) { # changer le prenom

						$user->setFirstname(htmlspecialchars($_POST['firstname']));
						# $_SESSION['user'] = $user; # update de session
						$user->save();
						#header("Refresh:0");
						$form = $user->formEditProfil(); # reaffichage du formulaire mis a jour
						$infos[] = "Votre prénom a été mis à jour !";
						$view->assign("infos", $infos);
					}

					if ($_POST['lastname'] != $user->getLastname()) { # changer le nom

						$user->setLastname(htmlspecialchars($_POST['lastname']));
						# $_SESSION['user'] = $user; # update de session
						$user->save();
						#header("Refresh:0");
						$form = $user->formEditProfil();
						$infos[] = "Votre nom a été mis à jour !";
						$view->assign("infos", $infos);
					}

					if ($_POST['country'] != $user->getCountry()) {

						$user->setCountry($_POST['country']); # options donc no need specialchars
						# $_SESSION['user'] = $user;
						$user->save();
						$form = $user->formEditProfil();
						$infos[] = "Votre pays a été mis à jour !";
						$view->assign("infos", $infos);
					}


					if (!empty($_POST['pwd'])) {

						if (!empty($_POST['pwdConfirm'])) {


							if ($_POST['pwd'] === $_POST['pwdConfirm']) {

								if (strlen($_POST['pwd']) > 7) {

									$pwd = password_hash($_POST['pwd'], PASSWORD_DEFAULT);

									$user->setPwd($pwd);
									#$_SESSION['user'] = $user; # update de session
									$infos[] = "Votre mot de passe a été mis à jour !";
									$view->assign("infos", $infos);
									$user->save();
								} else {
									$view->assign('errors', ["La taille du nouveau mot de passe doit faire 8 caractères au minimum."]);
								}
							} else {
								$view->assign("errors", ["La confirmation du mot de passe ne correspond pas."]);
							}
						} else {
							$view->assign("errors", ['Veuillez confirmer votre nouveau mot de passe.']);
						}
					}
				} else {
					$view->assign("errors", ["Le mot de passe actuel est erroné"]);
				}
			} else {
				$view->assign("errors", ['Veuillez indiquer votre mot de passe actuel.']);
			}

			#header("Location:editprofil");

		}

		$view->assign("form", $form); # affiche le formulaire


	}


	public function loginAction()
	{

		session_start();

		if (isset($_SESSION['id'])) header("Location:/"); # user deja connected

		$user = new User();

		$view = new View("login");

		$form = $user->formLogin();

		if (!empty($_POST['email'])) {

			$mailExists = $user->verifyMail($_POST['email']); # verify unicity in database

			if ($mailExists == 1) {

				$pwd = $user->verifyPwd($_POST['email']);
				# cherche le mdp correspond a ce mail en base
				if (password_verify($_POST['pwd'], $pwd)) {

					$user->setAllFromEmail($_POST['email']);
					# set tous les attributs depuis la base
					# à partir du mail

					if($user->isDeleted()){
						$view->assign("infos", ["Ce compte a été supprimé."]);
					

					# verify status USERVALIDATED : 2 else no login allowed
					}else if(($user->isValidated() || $user->isSuperAdmin()) && !$user->isDeleted()){

						$token = substr(md5(uniqid(true)), 0, 10); # cut length to 10, no prefix, entropy => for more unicity
						$user->setToken($token);

						$_SESSION['id'] = $user->getId();
						$_SESSION['email'] = $user->getEmail(); # email unique donc ca devrait etre bon
						# $_SESSION['pwd'] = $user->getPwd(); # ??
						$_SESSION['token'] = $token; # not sure

						header("Location:/lbly-admin"); # temporairement
						# $user->deleteAll(); # pour delete immediatement en 
					}else{
                        $email = $_POST['email'];
					    $html = "Votre compte n'a pas été validé.";
                        $view->assign("infos", [$html]);
//					    echo "Vous devez aller <strong style='color:red'>confirmer votre compte</strong> avec le mail que vous avez reçu à cette adresse : <strong style='color:blue'>".$user->getEmail()."</strong><br/>";
//
//						echo "<a href='http://localhost/userconfirm?email=$email'>Renvoyer le mail de confirmation</a>";
						# redirect here
					}


				} else {

					$form['inputs']['email']['value'] = $_POST['email']; # re remplissage du champ
					$view->assign("errors", ["Mot de passe erroné."]);
					#header("Location:/login");


				}
			} else {
				$view->assign("errors", ["Le mail n'existe pas."]);
			}
		}

		$view->assign("form", $form);
	}


	public function registerAction()
	{

		session_start();
		if(isset($_SESSION['id'])){
			$user = new User();
			$user->setAllFromId($_SESSION['id']);
		}


		$userRegister = new User();

		$view = new View("register");

		$form = $userRegister->formRegister();

		if (!empty($_POST)) {

			$errors = FormValidator::check($form, $_POST);

			$form['inputs']['email']['value'] = $_POST['email'];

			$form['inputs']['firstname']['value'] = $_POST['firstname'];

			$form['inputs']['lastname']['value'] = $_POST['lastname'];


			if (empty($errors)) {

				$mailExists = $userRegister->verifyMail($_POST['email'], $userRegister->getTable());
				# verify unicity in database


				if ($mailExists == 0) {

					if ($_POST['pwd'] == $_POST['pwdConfirm']) {

						$pwd = password_hash($_POST['pwd'], PASSWORD_DEFAULT);

						$userRegister->setFirstname(htmlspecialchars($_POST["firstname"]));
						$userRegister->setLastname(htmlspecialchars($_POST["lastname"]));
						$userRegister->setEmail(htmlspecialchars($_POST["email"]));
						$userRegister->setPwd($pwd);
						$userRegister->setCountry($_POST["country"]);

						$userRegister->setToken(Helpers::createToken());

						if(isset($user) && $user->isAdmin()){
							$userRegister->addStatus(USERVALIDATED);
							# envoi de mail au nouvel user créé pour quil change son pwd
							header("Location: /lbly-admin/adminview");


						}else{

							$email = $_POST['email'];
							header("Location: userconfirm?email=$email");

						}	

							$userRegister->save();


					}else{
						$view->assign("errors", ["Vos mots de passe sont différents."]);
					}
				} else {
					$view->assign("errors", ["Ce mail est déjà utilisé."]);
				}
			} else {
				$view->assign("errors", $errors);
			}
		}

		$view->assign("form", $form);
		//$view->assign("formLogin", $formLogin);
	}

	# send mail to confirm user then redirect to login
	public function userConfirmAction(){
		$user = new User();
		if(is_null($_GET['email']) || empty($_GET['email']))
			header("Location: /");

		$user->setAllFromEmail($_GET['email']);

		$mailing = Mailing::getMailing();
		$mailing->mailConfirm($user);
		$mailing->setRecipient(MAILUSERNAME);
		$mailing->sendMail();
		header("Location: /lbly-admin/login");
		
	}

	public function logoutAction()
	{

		$security = new Secu();

		if ($security->isConnected()) session_destroy();
		header("Location:/");
	}
}
