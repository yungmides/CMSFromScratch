<?php

namespace App\Controller;

use App\Core\View;
use App\Core\Security;
use App\Models\Article;

class ArticleController{


	public function defaultAction(){

		$user = Security::getConnectedUser();
		if(is_null($user)) header("Location:/lbly-admin/login");

		$view = new View("articles","back");
		$article = new Article();


        if(isset($_GET['articleid'])){
        	$article->setAllById(htmlspecialchars($_GET['articleid']));

        	if(isset($_GET['publish'])){
        		$article->setStatus("publish");
        		$article->save();
        		$view->assign("infos", ["Article publié."]);

        	}

        	if(isset($_GET['withdraw'])){
        		$article->setStatus("withdraw");
        		$article->save();
        		$view->assign("infos", ["Article retiré."]);
        		
        	}
        }

        $articles = $article->all();
        $view->assign("articles", $articles);

	}


	public function addArticleAction(){
		$user = Security::getConnectedUser();
		if(is_null($user)) header("Location:/lbly-admin/login");
		// session_start();
		// if(!isset($_SESSION['id'])) header("Location:/lbly-admin/login"); # si user non connecté => redirection
		// $user = $_SESSION['user'];


		$view = new View("addArticle","back");
		$article = new Article();

		if (!empty($_POST)) {
			
		    if (empty($errors)){
				$article->setTitle(htmlspecialchars($_POST['title']));
				$article->setAuthor($user->getID());
                $categories = "";
                foreach ($_POST['category'] as $item) {
                    $categories .= $item . ",";
                }
                $categories = substr($categories,0,-1);
                $article->setCategory(htmlspecialchars($categories));
				$article->setContent($_POST['content']);
				if (empty($_POST['content'])){
					$view->assign("errors", ["Veuillez remplir tous les champs"]);
				}else{
					$article->setSlug($article->title2slug($_POST['title']));
					if (empty($article->getAllBySlug($article->getSlug()))){
						$article->setStatus("withdraw");
						$article->save();
						header("Location:/lbly-admin/articles");
					}else{
						$view->assign("errors", ["Veuillez changer le titre de votre article"]);
					}
				}
            }else{
                $view->assign("errors", $errors);
            }
        }

		$form = $article->formAddArticle();
		$view->assign("form", $form);
		
	}

	public function editArticleAction()
	{
		$user = Security::getConnectedUser();
		if(is_null($user)) header("Location:/lbly-admin/login");

		$view = new View("editArticle","back"); # appelle View/editProfil.view.php
		$article = new Article();

		$uriExploded = explode("?", $_SERVER["REQUEST_URI"]);
        $uri = substr($uriExploded[0], 26);

		$article->setAllBySlug($uri);
		$form = $article->formEditArticle();

		if(!empty($_POST)){
			if($_POST['title'] != $article->getTitle()){ # changer le prenom

				if (!empty($_POST['title'])){
					$article->setTitle(htmlspecialchars($_POST['title']));
					$article->setSlug($article->title2slug($_POST['title']));
					if (empty($article->getAllBySlug($article->getSlug()))){
						$article->save();
						$form = $article->formEditArticle();
						$infos[] = "Le titre a été mis à jour !";
						$view->assign("infos", $infos);
					}else{
						echo $article->getSlug();
						$view->assign("errors", ["Veuillez changer le titre de votre article"]);
					}
				}else{
					$view->assign("errors", ["Veuillez remplir tous les champs"]);
				}
			}
            $categories = "";
            foreach ($_POST['category'] as $item) {
                $categories .= $item . ",";
            }
            $categories = substr($categories,0,-1);

            if (!empty($categories)){
                if ($categories != $article->getCategory()){
                    $categories = "";
                    foreach ($_POST['category'] as $item) {
                        $categories .= $item . ",";
                    }
                    $categories = substr($categories,0,-1);
                    $article->setCategory(htmlspecialchars($categories ));
                    $article->save();
                    $form = $article->formEditArticle();
                    $infos[] = "Le contenu a été mis à jour !";
                    $view->assign("infos", $infos);
                }
            }else{
                $view->assign("errors", ["Veuillez choisir une categorie"]);
            }

			if($_POST['content'] != $article->getContent()){ # changer le nom

				if (!empty($_POST['content'])){
					$article->setContent($_POST['content']);
					$article->save();
					$form = $article->formEditArticle();
					$infos[] = "Le contenu a été mis à jour !";
					$view->assign("infos", $infos);
				}else{
					$view->assign("errors", ["Veuillez remplir tous les champs"]);
				}
			}
		}
		$view->assign("form", $form); # affiche le formulaire
	}

	public function deleteArticleAction(){

		$user = Security::getConnectedUser();
		if(is_null($user)) header("Location:/lbly-admin/login");

		$view = new View("articles","back");
		$article = new Article();
		$articles = $article->all();

		$uriExploded = explode("?", $_SERVER["REQUEST_URI"]);
        $uri = substr($uriExploded[0], 28);

        $article->setAllBySlug($uri);
        $articlecontent = $article->getAllBySlug($uri)[0];

		if (!empty($_POST["delete"])){
            $article->deleteBySlug($uri);
            header("Location:/lbly-admin/articles");
        }

        $view->assign("articles", $articles);
        $view->assign("article", $articlecontent);
        $view->assign("deletemodal", true);

		// var_dump($article->formDeleteArticle());
        $formdelete = $article->formDeleteArticle();
        $view->assign("formdelete", $formdelete);
		


	}


	public function seeArticleAction(){

		session_start();

        $article = new Article();

        $view = new View("seeArticle", "front");

        $uriExploded = explode("?", $_SERVER["REQUEST_URI"]);

        $uri = substr($uriExploded[0], 10);
        $article->setAllBySlug($uri);
        $articles = $article->getAllBySlug($uri);

        if($article->getStatus() == 'publish')
	        $view->assign("article", $articles[0]);
        else
        	header("Location: /lbly-admin/articles");


    }


}