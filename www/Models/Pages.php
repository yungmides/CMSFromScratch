<?php 

namespace App\Models;

use App\Core\Singleton;
use PDO;

class Pages extends Singleton
{
	private $id = null;
    protected $title;
    protected $content;
    protected $createdBy;
    protected $slug;
    private $table = DBPREFIX . "page";

    public function __construct(){

    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }


    public function title2slug($title){
        $title = preg_replace('~[^\pL\d]+~u', '-', $title);
        //retire symboles spéciaux
        $title = iconv("UTF-8", "ASCII//TRANSLIT", $title);

        $title = preg_replace('~[^-\w]+~', '', $title);

        $title = trim($title, '-');
        //suprimme double -
        $title = preg_replace('~-+~', '-', $title);
        //minuscule
        $title = strtolower($title);

        return $title;
    }

	public function formAddPage()
	{

		return [

            "config" => [
                "method" => "POST",
                "action" => "",
                "id" => "form_addpage",
                "class" => "form_builder",
                "submit" => "Ajouter"
            ],
            "inputs" => [
                "title" =>[
                    "type" => "text",
                    "label" => "Titre : ",
                    "minLength" => 2,
                    "maxLength" => 60,
                    "id" => "title",
                    "class" => "title",
                    "placeholder" => "Titre de votre page",
                    "value" => '',
                    "error" => "Votre titre doit faire entre 2 et 60 caractères",
                    "required" => true
                ],
                "editor" => [
                    "type" => "textarea",
                    "label" => "",
                    "cols" => 80,
                    "rows" => 10,
                    "id" => "editor",
                    "name" => "editor",
                    "value" => '',
                    "error" => "probleme enregistrement base de données"
                ]
            ]
        ];
	}

}