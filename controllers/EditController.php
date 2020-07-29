<?php
    /* ----------------------------------------------------------------------
     * Article editor (Articles) plugin for Providence
     * ----------------------------------------------------------------------
     * List & list values editor plugin for Providence - CollectiveAccess
     * Open-source collections management software
     * ----------------------------------------------------------------------
     *
     * Plugin by idéesculture (www.ideesculture.com)
     * This plugin is published under GPL v.3. Please do not remove this header
     * and add your credits thereafter.
     *
     * File modified by :
     * ----------------------------------------------------------------------
     */
    ini_set("display_errors", 1);
    error_reporting(E_ERROR);
    require_once(__CA_MODELS_DIR__.'/ca_lists.php');
    require_once(__CA_MODELS_DIR__.'/ca_objects.php');
    require_once(__CA_MODELS_DIR__.'/ca_site_pages.php');
    require_once(__CA_MODELS_DIR__.'/ca_entities.php');
    require_once(__CA_MODELS_DIR__.'/ca_places.php');

    require_once(__CA_MODELS_DIR__.'/ca_occurrences.php');
    require_once(__CA_MODELS_DIR__.'/ca_list_items.php');
    require_once(__CA_MODELS_DIR__.'/ca_object_labels.php');
    require_once(__CA_LIB_DIR__."/Search/EntitySearch.php");
    require_once(__CA_LIB_DIR__."/Search/CollectionSearch.php");
	error_reporting(E_ERROR);

 	class EditController extends ActionController
    {
        # -------------------------------------------------------
        protected $opo_config;        // plugin configuration file
        private $plugin_path;
        private $vs_theme;
        # -------------------------------------------------------
        # Constructor
        # -------------------------------------------------------

        public function __construct(&$po_request, &$po_response, $pa_view_paths = null)
        {
            parent::__construct($po_request, $po_response, $pa_view_paths);

            $this->plugin_path = __CA_APP_DIR__ . '/plugins/Articles';

            if (is_file(__CA_THEME_DIR__.'/conf/articles.conf')) {
                $this->opo_config = Configuration::load(__CA_THEME_DIR__.'/conf/articles.conf');
            } elseif (is_file(__CA_THEME_DIR__.'/conf/local/contribuer.conf')) {
                $this->opo_config = Configuration::load(__CA_THEME_DIR__.'/conf/local/articles.conf');
            } else {
                $this->opo_config = Configuration::load(__CA_APP_DIR__.'/plugins/Articles/conf/articles.conf');
            }
        }

        # -------------------------------------------------------
        # Functions to render views
        # -------------------------------------------------------
        public function Form()
        {
            // Exiting if anonymous contributions are not allowed
            if (!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
                //$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
                die("redirection...");
            }

            $id = $this->request->getParameter("id", pInteger);
            $table = "ca_site_pages";
            $this->view->setVar("table", $table);
            // No type but a template_id for website pages
            $template = $this->request->getParameter("template", pString);
            $this->view->setVar("template", $template);

            // TODO : go from "templage" to "template_id" with table ca_site_templates
            $this->view->setVar("template_id", 1);

            $parent_id = $this->request->getParameter("parent_id", pString);
            $this->view->setVar("parent_id", $parent_id);

            $this->view->setVar("template", $this->opo_config->get("template"));
            $mappings = $this->opo_config->get("form");

            // If we have parent_id, we need to override the template to disallow direct selection
            if ($parent_id) {
                foreach ($mappings[$table][$template] as $key => $mapping) {
                    $target = explode(".", $mapping["mapping"])[1];
                    if ($target == "parent_id") {
                        unset($mapping["dataSource"]);
                        $mapping["options"] = ["type" => "hidden"];
                        $mapping["default"] = $parent_id;
                        $mappings[$table][$template][$key] = $mapping;
                        break;
                    }
                }
            }
            $this->view->setVar("mappings", $mappings[$table][$template]);
            $mapping = $mappings[$table][$template];
            $label = "create a new article";
            $this->view->setVar("label", $label);

            $this->view->setVar("user_id", $this->request->getUserID());
            $this->view->setVar("timecode", time());
            $this->render('Pages/addform_html.php');
        }

        public function ShowList() {
            $all_articles = ca_site_pages::getPageList();
            $all_articles = array_reverse($all_articles);
            $articles = [];
            foreach ($all_articles as $testarticle) {
                if ($testarticle["template_title"]=="article") {
                    $articles[] = $testarticle;
                }
            }
            foreach ($articles as $key=>$art) {
                $page = new ca_site_pages($art["page_id"]);
                $article = $page->get("content");
                $articles[$key]["article"] = $article;
            }
            //$page = new ca_site_pages(1);
            $this->view->setVar("articles", $articles);
            $this->view->setVar("title", $this->opo_config->get("list_title"));

            $this->render('Pages/list_html.php');
        }

        public function EditForm() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
                //$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }
            $id= $this->request->getParameter("id", pInteger);
            $this->view->setVar("id", $id);
            $table = "ca_site_pages";
            $this->view->setVar("table", $table);
            // No type but a template_id for website pages
            $template = $this->request->getParameter("template", pString);
            $this->view->setVar("template", $template);
            $parent_id = $this->request->getParameter("parent_id", pString);
            $this->view->setVar("parent_id", $parent_id);
            $id = $this->request->getParameter("id", pString);
            $this->view->setVar("id", $id);
            if(!$id) {
                //$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $vt_page = new ca_site_pages($id);

            $this->view->setVar("template", $this->opo_config->get("template"));
            $mappings = $this->opo_config->get("form");

            $this->view->setVar("mappings", $mappings[$table][$template]);

            $data = [];
            //var_dump($mappings[$table][$template]);die();
            foreach($mappings[$table][$template] as $name=>$mapping) {
                $value = $vt_page->get($mapping["mapping"]);
                if($mapping["type"]=="array") {
                    $value = explode(";", $value);
                }
                if($value) { $data[$name] = $value; }
            }

            $this->view->setVar("data", $data);

            $label = "Edit article";
            $this->view->setVar("label", $label);

            $this->view->setVar("user_id", $this->request->getUserID());
            $this->view->setVar("timecode", time());
            $this->render("Pages/editform_html.php");
        }

        public function Save() {
            error_reporting(E_ERROR);
	        $table = "ca_site_pages";
	        $mappings = $this->opo_config->get("form");
            $id= $this->request->getParameter("id", pInteger);
            $vt_page = new ca_site_pages($id);

            $template = $this->request->getParameter("template", pString);
            if(!$template) {
                $template = (int) $vt_page->get('ca_site_pages.template_id');
                if($template == 1) $template="article";
            }
            if(!$mappings[$table][$template]) {
                die("This template does not allow direct edition, please modify your contribuer.conf for this template to allow it.");
            }

	        $this->view->setVar("mappings", $mappings[$table][$template]);
            $content=[];
	        foreach($mappings[$table][$template] as $name=>$mapping) {
	            // Data is a content subblock, create a $content array with the values
	            if(strpos($mapping["mapping"], "ca_site_pages.content") === 0) {
	                // Fetching the posted value
	                $value = $this->request->getParameter($name, pString);
	                $target = str_replace("ca_site_pages.content.", "", $mapping["mapping"]);
                    $content[$target]=$value;
	                // Setting the field value
                }
            }
	        $vt_page->setMode(ACCESS_WRITE);
	        $vt_page->set("ca_site_pages.content", $content);

            $label = "Saved article";
            $this->view->setVar("label", $label);

            $this->view->setVar("user_id", $this->request->getUserID());
            $this->view->setVar("timecode", time());
            $vt_page->update();
            //$this->render('Pages/saved_html.php');
            $vt_page->update();
            $this->response->setRedirect(caNavUrl($this->request, "Articles", "Show", "Details", ["id"=> $id]));
            //http://phoi.ideesculture.test/index.php/Articles/Show/Details/id/1
        }

    }
