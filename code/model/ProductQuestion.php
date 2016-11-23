<?php

/**
 * Define the Product Questions ...
 *
 * @package ecommerce
 * @subpackage ProductQuestion
 */
class ProductQuestion extends DataObject
{

    /**
     * Standard SS variable.
     */
    private static $db = array(
        'InternalCode' => 'Varchar(30)',
        'Question' => 'Varchar(255)',
        'AnswerRequired' => 'Boolean',
        'Label' => 'Varchar(100)',
        'DefaultAnswer' => 'Varchar(150)',
        'DefaultFormField' => 'Varchar(255)',
        'Options' => 'Text',
        "HasImages" => "Boolean",
        "ApplyToAllProducts" => "Boolean"
    );

    /**
     * Standard SS variable.
     */
    private static $casting = array(
        'Title' => 'Varchar',
        'FullName' => 'Varchar'
    );

    /**
     * Standard SS variable.
     * Links questions to products
     */
    private static $many_many = array(
        'Products' => 'Product'
    );

    /**
     * Standard SS variable.
     * Links to folder for images
     */
    private static $has_one = array(
        'Folder' => 'Folder'
    );

    /**
     * Standard SS variable.
     * Links questions to products
     */
    private static $summary_fields = array(
        'InternalCode' => 'InternalCode',
        'Question' => 'Question'
    );

    /**
     * Standard SS variable.
     * Links questions to products
     */
    private static $searchable_fields = array(
        'InternalCode' => 'PartialMatch',
        'Question' => 'Varchar(255)',
        'Label' => 'PartialMatch',
        'DefaultAnswer' => 'PartialMatch',
        'DefaultFormField' => 'PartialMatch',
        'Options' => 'PartialMatch',
        "HasImages" => true
    );

    /**
     * Standard SS variable.
     */
    private static $default_sort = "\"Question\" ASC";

    /**
     * Standard SS variable.
     */
    private static $defaults = array(
        "DefaultFormField" => "TextField",
        "DefaultAnswer" => "tba"
    );

    /**
     * form fields used without a list
     * @var array
     */
    private static $available_form_fields_free = array(
        "TextField" => "Text Field",
        "TextareaField" => "Text Field with several lines",
        "DateField" => "Date Field",
        "EmailField" => "Email Field"
    );

    /**
     * form fields used with a list
     * @var array
     */
    private static $available_form_fields_list = array(
        "DropdownField" => "Dropdown Field",
        "OptionSetField" => "Option list Field"
    );

    /**
     * Maximum number of products on the site before the CheckboxSetField is
     * replaced with a GridField.
     * This field is used for selecting the products the question applies to.
     *
     * @var Int
     */
    private static $max_products_for_checkbox_set_field = 300;

    /**
     * Standard SS variable.
     */
    private static $singular_name = "Product Question";
    public function i18n_singular_name()
    {
        return _t("ProductQuestion.PRODUCT_QUESTION", "Product Question");
    }

    /**
     * Standard SS variable.
     */
    private static $plural_name = "Product Variations";
    public function i18n_plural_name()
    {
        return _t("ProductQuestion.PRODUCT_QUESTIONS", "Product Questions");
    }
    public static function get_plural_name()
    {
        $obj = Singleton("ProductQuestion");
        return $obj->i18n_plural_name();
    }

    /**
     * turns an option into potential file names
     * e.g. red
     * returns
     * red.jpg, red.png, red.gif
     * @param String $option
     * @return Array
     */
    public static function create_file_array_from_option($option)
    {
        $option = str_replace(' ', "-", trim($option));
        $option = preg_replace("/[^a-z0-9_-]+/i", "", $option);
        $imageOptions = array(
            $option.".png",
            $option.".PNG",
            $option.".gif",
            $option.".GIF" ,
            $option.".jpg",
            $option.".JPG",
            $option.".jpeg",
            $option.".JPEG"
        );
        return $imageOptions;
    }

    /**
     * Standard SS method
     * @return FieldSet
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab("Root.Images", new CheckboxField("HasImages", _t("ProductQuestion.HAS_IMAGES", "This question makes use of images")));
        $fields->addFieldToTab("Root.Products", new CheckboxField("ApplyToAllProducts", _t("ProductQuestion.APPLY_TO_ALL_PRODUCTS", "Apply to all products ...")));
        if (!$this->HasImages) {
            $fields->addFieldToTab(
                "Root.DefaultFormField",
                new OptionSetField(
                    "DefaultFormField",
                    _t("ProductQuestion.DEFAULTFORMFIELD", "Field type to use"),
                    $this->Options ? $this->Config()->get("available_form_fields_list") : $this->Config()->get("available_form_fields_free")
                )
            );
        } else {
            $fields->removeFieldFromTab("Root.Main", "DefaultFormField");
        }
        $productFieldTitle = _t("ProductQuestion.PRODUCTS", "Products showing this question");
        if ($this->ApplyToAllProducts) {
            $productField = new HiddenField("Products");
        } elseif (Product::get()->count() < $this->Config()->get("max_products_for_checkbox_set_field")) {
            $productField = new CheckboxSetField("Products", $productFieldTitle, Product::get()->map("ID", "FullName")->toArray());
        } else {
            $productField = new GridField(
                'Products',
                _t("ProductQuestion.PRODUCTS", "Products showing this question"),
                $this->Products(),
                GridFieldEditOriginalPageConfigWithDelete::create()
            );
        }
        $fields->addFieldToTab("Root.Products", $productField);
        if (!$this->ApplyToAllProducts) {
            foreach ($this->Products() as $product) {
                $fields->addFieldToTab(
                    "Root.Products",
                    new LiteralField(
                        "Product".$product->ID,
                        "<h5><a href=\"".$product->CMSEditLink()."\">"._t("ProductQuestion.BACK_TO", "Edit ")." ".$product->Title."</a></h5>"
                    )
                );
            }
        }
        if ($this->HasImages) {
            $folders = Folder::get();
            if ($folders->count()) {
                $folderMap = $folders->map("ID", "Title")->toArray();
                $folders = null;
                $fields->removeFieldFromTab("Root.Main", "Folder");
                $fields->addFieldToTab(
                    "Root.Images",
                    $treeDropdownField = new TreeDropdownField("FolderID", _t("ProductQuestion.FOLDER", "Folder"), "Folder")
                );
                $treeDropdownFieldRightTitle = _t(
                    "ProductQuestion.FOLDER_ID",
                    "Select the folder in which the images live.
                    <br />
                    <strong>
                        The images need to have the exact same file name as the options listed.
                        For example, if one of your options is 'red' then there should be a file in your folder called 'red.png' or 'red.jpg' or 'red.gif',
                        the following filenames would not work: 'Red.png', 'red1.jpg', 'RED.gif', etc...
                    </strong>");
                $folder = Folder::get()->byID($this->FolderID);
                if ($folder) {
                    $treeDropdownFieldRightTitle .= "
                        <br /><a href=\"admin/assets/show/".$folder->ID."/\">"._t("ProductQuestion.OPEN", "Open")." ".$folder->Title."</a>
                        <br /><a href=\"admin/assets/add/?ID".$folder->ID."/\">"._t("ProductQuestion.ADD_IMAGES_TO", "Add images to")." ".$folder->Title."</a>";
                }
                $treeDropdownField->setRightTitle($treeDropdownFieldRightTitle);
            }
            if ($this->FolderID) {
                $imagesInFolder = Image::get()->filter(array("ParentID" => $this->FolderID));
                if ($imagesInFolder->count()) {
                    $imagesInFolderArray = $imagesInFolder->map("ID", "Name")->toArray();
                    $options = explode(",", $this->Options);
                    $imagesInFolderField = new ReadonlyField("ImagesInFolder", _t("ProductQuestion.NO_IMAGES", "Images in folder"), implode("<br />", $imagesInFolderArray));
                    $imagesInFolderField->dontEscape = true;
                    $fields->addFieldToTab("Root.Images", $imagesInFolderField);
                    //matches
                    if ($this->exists()) {
                        $matchesInFolderArray = array();
                        $nonMatchesInFolderArray = array();
                        $options = explode(",", $this->Options);
                        if (count($options)) {
                            foreach ($options as $option) {
                                $fileNameArray = self::create_file_array_from_option($option);
                                foreach ($fileNameArray as $fileName) {
                                    if (in_array($fileName, $imagesInFolderArray)) {
                                        $matchesInFolderArray[$option] = "<strong>".$option."</strong>: ".$fileName;
                                    }
                                }
                                if (!isset($matchesInFolderArray[$option])) {
                                    $nonMatchesInFolderArray[$option] = "<strong>".$option." - add one these files: </strong>".implode(",", $fileNameArray);
                                }
                            }
                        }
                        $matchesInFolderField = new ReadonlyField("MatchesInFolder", _t("ProductQuestion.MATCHES_IN_FOLDER", "Matches in folder"), implode("<br />", $matchesInFolderArray));
                        $matchesInFolderField->dontEscape = true;
                        $fields->addFieldToTab("Root.Images", $matchesInFolderField);
                        $nonMatchesInFolderField = new ReadonlyField("NonMatchesInFolder", _t("ProductQuestion.NON_MATCHES_IN_FOLDER", "NON Matches in folder"), implode("<br />", $nonMatchesInFolderArray));
                        $nonMatchesInFolderField->dontEscape = true;
                        $fields->addFieldToTab("Root.Images", $nonMatchesInFolderField);
                    }
                } else {
                    $imagesInFolderField = new ReadonlyField("ImagesInFolder", "Images in folder", _t("ProductQuestion.NO_IMAGES", "There are no images in this folder"));
                    $fields->addFieldToTab("Root.Main", $imagesInFolderField);
                }
            }
        } else {
            $fields->removeByName("Folder");
        }
        if ($this->Products()->count()) {
            $randomProduct = $this->Products()->First();
            $fields->addFieldToTab("Root.Example", $this->getFieldForProduct($randomProduct));
        }
        $folderExplanation = _t(
            "ProductQuestion.FOLDER_EXPLANATION",
            "Tick the box to link each option to an image (e.g. useful if you have colour swatches).
                Once ticked and the question is saved you will be able to select a folder from where to select the images.");
        $field = $fields->dataFieldByName("HasImages");
        if ($field) {
            $field->setDescription($folderExplanation);
        }

        $this->extend('updateCMSFields', $fields);

        //custom field labels
        $internalCodeField = $fields->dataFieldByName("InternalCode");
        $internalCodeField->setRightTitle(_t("ProductQuestion.INTERNALCODE", "Code used to identify question (not shown to customers)"));
        $questionField = $fields->dataFieldByName("Question");
        $questionField->setRightTitle(_t("ProductQuestion.QUESTION", "Question (e.g. what configuration do you prefer?)"));
        $labelField = $fields->dataFieldByName("Label");
        $labelField->setRightTitle(_t("ProductQuestion.LABEL", "Label (e.g. Your selected configuration)"));
        $defaultAnswerField = $fields->dataFieldByName("DefaultAnswer");
        $defaultAnswerField->setRightTitle(_t("ProductQuestion.DEFAULT_ANSWER", "Default Answer if no Answer has been provided.  Can be blank or, for example, tba."));
        $optionsField = $fields->dataFieldByName("Options");
        $optionsField->setRightTitle(_t("ProductQuestion.OPTIONS", "Predefined Options (leave blank for any option).  These must be comma separated (e.g. red, blue, yellow, orange)"));
        return $fields;
    }



    /**
     * casted variable
     * @return String
     */
    public function FullName()
    {
        return $this->getFullName();
    }
    public function getFullName()
    {
        return $this->Question." (".$this->InternalCode.")";
    }

    /**
     * casted variable
     * @return String
     */
    public function Title()
    {
        return $this->getTitle();
    }
    public function getTitle()
    {
        return $this->Question." (".$this->InternalCode.")";
    }

    /**
     *
     * @return FormField
     */
    public function getFieldForProduct($product, $value = null)
    {
        //switch from variation to product ...
        if ($product instanceof ProductVariation) {
            $product = $product->getParent();
        }
        if ($this->Options) {
            //if HasImages?
            $finalOptions = array();
            $optionArray = explode(",", $this->Options);
            foreach ($optionArray as $option) {
                $option = trim($option);
                $finalOptions[Convert::raw2htmlatt($option)] = $option;
            }
            if ($this->HasImages) {
                return new ProductQuestionImageSelectorField($this->getFieldForProductName($product), $this->Question, $finalOptions, $value, $this->FolderID);
            } else {
                $formFieldClass = $this->DefaultFormField;
                if (!$formFieldClass) {
                    $formFieldClass = "DropdownField";
                }
                $finalOptions = array("" => _t("ProductQuestion.PLEASE_SELECT", " -- please select --")) + $finalOptions;
                return $formFieldClass::create($this->getFieldForProductName($product), $this->Question, $finalOptions, $value);
            }
        } else {
            $formFieldClass = $this->DefaultFormField;
            if (!$formFieldClass) {
                $formFieldClassd = "TextField";
            }
            return $formFieldClass::create($this->getFieldForProductName($product), $this->Question, $value);
        }
    }

    public function getFieldForProductName(Product $product)
    {
        return "ProductQuestions[".$this->ID."]";
    }

    /**
     *
     * making sure all the data is clean
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->InternalCode) {
            $this->InternalCode = $this->Label;
        }
        if (!$this->HasImages) {
            $this->FolderID = 0;
        }
        if ($this->Options) {
            $optionsForFields = $this->Config()->get("available_form_fields_list");
            if (!isset($optionsForFields[$this->DefaultFormField])) {
                //get the first one if none is set.
                $this->DefaultFormField = key($optionsForFields);
            }
        } else {
            $optionsForFields = $this->Config()->get("available_form_fields_free");
            if (!isset($optionsForFields[$this->DefaultFormField])) {
                //get the first one if none is set.
                $this->DefaultFormField = key($optionsForFields);
            }
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->ApplyToAllProducts) {
            $this->Products()->removeAll();
        }
    }

    /**
     * link to edit the record
     * @param String | Null $action - e.g. edit
     * @return String
     */
    public function CMSEditLink($action = null)
    {
        return Controller::join_links(
            Director::baseURL(),
            "/admin/product-config/".$this->ClassName."/EditForm/field/".$this->ClassName."/item/".$this->ID."/edit",
            $action
        );
    }
}
