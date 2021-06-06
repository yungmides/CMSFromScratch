<?php

namespace App\Models;

use App\Core\Singleton;
use PDO;

class Book extends Singleton
{
    private $id = null;
    protected $title;
    protected $description;
    protected $author;
    protected $publication_date;
    protected $image;
    protected $publisher;
    protected $price = 0;
    protected $category;
    protected $stock_number = 0;

    private $table = DBPREFIX . "books";
    public function __construct()
    {
    }
    # set all properties from database from the email
    public function setAll($id)
    {
        $this->id = $id;
        $query = "SELECT * FROM " . $this->table . " WHERE id = '" . $id . "'";
        $prepare = $this->getPDO()->prepare($query);
        $prepare->execute();
        $res = $prepare->fetch(PDO::FETCH_ASSOC);
        $this->setId($id);
        $this->setTitle($res["title"]);
        $this->setDescription($res["description"]);
        $this->setAuthor($res["author"]);
        $this->setPublicationDate($res["publication_date"]);
        $this->setImage($res["image"]);
        $this->setPublisher($res["publisher"]);
        $this->setPrice($res["price"]);
        $this->setCategory($res["category"]);
        $this->setStockNumber($res["stock_number"]);
    }

    public function stockUp($number = 1)
    {
        $this->setStockNumber($this->getStockNumber() + $number);
    }
    public function stockDown($number = 1)
    {
        $this->setStockNumber($this->getStockNumber() - $number);
    }

    // Forms

    public function formAddBook()
    {
        return [
            "config" => [
                "method" => "POST",
                "action" => "",
                "id" => "form_editprofil",
                "class" => "form_builder",
                "submit" => "Valider"
            ],
            "inputs" => [
                "title" => [
                    "type" => "text",
                    "label" => "Titre du livre",
                    "minLength" => 1,
                    "maxLength" => 100,
                    "id" => "title",
                    "class" => "form_input",
                    "placeholder" => "Exemple: Harry Potter et La Coupe de Feu",
                    "value" => $this->title ?? "",
                    "error" => "Le titre doit faire entre 1 et 100 caractères ",
                    "required" => true
                ],
                "description" => [
                    "type" => "text",
                    "label" => "Description du livre",
                    "minLength" => 1,
                    "maxLength" => 255,
                    "id" => "description",
                    "class" => "form_input",
                    "placeholder" => "Un super livre",
                    "value" => $this->description ?? "",
                    "error" => "Votre description doit faire entre 1 et 255 caractères",
                    "required" => true
                ],
                "author" => [
                    "type" => "text",
                    "label" => "Nom de l'auteur",
                    "minLength" => 1,
                    "maxLength" => 100,
                    "id" => "author",
                    "class" => "form_input",
                    "placeholder" => "Exemple: Sun Tzu",
                    "value" => $this->getAuthor() ?? '',
                    "error" => "Le nom de l'auteur doit faire entre 1 et 320 caractères",
                    "required" => true,
                ],
                "publication_date" => [
                    "type" => "date",
                    "label" => "Date de publication",
                    "id" => "publication_date",
                    "class" => "form_input",
                    "placeholder" => "",
                    "error" => "La date rentrée est incorrecte",
                    "required" => true
                ],
                "image" => [
                    "type" => "file",
                    "label" => "Image de couverture du livre",
                    "accept" => "image/*",
                    "id" => "image",
                    "class" => "form_input",
                    "placeholder" => "",
                    "error" => "Le fichier envoyé est incorrect",
                    "required" => false
                ],
                "publisher" => [
                    "type" => "text",
                    "label" => "Maison d'édition",
                    "minLength" => 1,
                    "maxLength" => 100,
                    "id" => "publisher",
                    "class" => "form_input",
                    "placeholder" => "Exemple: Flammarion",
                    "error" => "La maison d'édition doit faire entre 1 et 100 caractères",
                    "required" => true
                ],
                "price" => [
                    "type" => "number",
                    "label" => "Prix de vente",
                    "min" => 1,
                    "step" => "any",
                    "id" => "price",
                    "class" => "form_input",
                    "placeholder" => "200",
                    "error" => "Le prix doit être au moins supérieur à 1€",
                    "required" => true
                ],
                //category g pas encore fait, faudra faire un options
                // Pour l'instant on a un input type text
                "category" => [
                    "type" => "text",
                    "label" => "Catégorie",
                    "minLength" => 1,
                    "maxLength" => 100,
                    "id" => "category",
                    "class" => "form_input",
                    "placeholder" => "Exemple: Science-fiction",
                    "error" => "La catégorie doit faire entre 1 et 100 caractères",
                    "required" => true
                ],
                "stock_number" => [
                    "type" => "number",
                    "label" => "Nombre de livres en stock",
                    "min" => 1,
                    "id" => "stock_number",
                    "class" => "form_input",
                    "placeholder" => "200",
                    "error" => "Le nombre de livres doit être au moins supérieur à 1",
                    "required" => true
                ],

            ]
        ];
    }

    // Getters & Setters
    public function getTable()
    {
        return $this->table;
    }
    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function setTitle($title)
    {
        $this->title = $title;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }
    public function getAuthor()
    {
        return $this->author;
    }
    public function setAuthor($author)
    {
        $this->author = $author;
    }
    public function getPublicationDate()
    {
        return $this->publication_date;
    }
    public function setPublicationDate($publication_date)
    {
        $this->publication_date = $publication_date;
    }
    public function getImage()
    {
        return $this->image;
    }
    public function setImage($image)
    {
        $this->image = $image;
    }
    public function getPublisher()
    {
        return $this->publisher;
    }
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }
    public function getPrice()
    {
        return $this->price;
    }
    public function setPrice($price)
    {
        $this->price = $price;
    }
    public function getCategory()
    {
        return $this->category;
    }
    public function setCategory($category)
    {
        $this->category = $category;
    }
    public function getStockNumber()
    {
        return $this->stock_number;
    }
    public function setStockNumber($stock_number)
    {
        $this->stock_number = $stock_number >= 0 ? $stock_number : 0;
    }
}
