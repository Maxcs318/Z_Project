<?php header('Access-Control-Allow-Origin: *'); ?>
<?php
    class Article extends CI_Controller
    {
        // public $JSON_DATA;
        public function __construct()
        {
            parent::__construct();
            $this->load->model("article_model");
            $this->load->model('../../Check_/models/Check__model');
            $this->load->model('../../Files_Upload/models/Files_Upload_model');
            $this->output->set_content_type("application/json", 'utf-8');
            // $this->output->set_header("Access-Control-Allow-Origin: *");
            $this->output->set_header("Access-Control-Allow-Methods: GET, POST , OPTIONS");
            $this->output->set_header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");

            $this->load->helper("URL", "DATE", "URI", "FORM");//
            $this->load->library('form_validation');//

        }
        // get all Article
        public function get_all_article()
        {
            echo $this->article_model->get_all_article();
        }
        // get all Article_Category
        public function get_all_article_category()
        {
            echo $this->article_model->get_all_article_category();
        }
        // insert Article
        public function insert_article()
        {
            // check status for insert
            $creator = json_decode($this->input->post('creator'));
            if($creator==null || $creator==''){
                echo 'fail';
                exit;
            }
            $creatorID  = $this->Check__model->chk_token($creator);
            $statusUser = $this->Check__model->chk_status($creatorID);
            if( $statusUser != 'admin' ){
                echo 'fail';
                exit ;
            }
            // set key
            $key_link = 'A'.date('dmYHis').substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,10))), 1, 10);
            // insert image & article
            $article = (array)json_decode($this->input->post('article'));
                $ranSTR = date('dmYHis').substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,10))), 1, 10);
                $nameF = substr(strrev($_FILES['userfile']['name']), 0, strrpos(strrev($_FILES['userfile']['name']),"."));
                $typeF = strrev($nameF);
                $_FILES['userfile']['name'] = $ranSTR.'.'.$typeF;
                $config = array(
                    'upload_path'   => './../client/src/assets/Article/',
                    'allowed_types' => '*',
                    'max_size'      => '0',
                );
                $this->load->library('upload', $config,'file_image');
                $this->file_image->initialize($config);
                if ($this->file_image->do_upload('userfile')){
                    $data = array('upload_data' => $this->file_image->data());   
                    $article['a_image'] = $_FILES['userfile']['name'];
                    $article['a_create_date'] = $this->Check__model->date_time_now();
                    $article['a_file_key'] = $key_link;
                    $thisID = $this->article_model->insert_article($article);
                    $article['a_id']=$thisID;
                }else{
                    $error = array('error' => $this->upload->display_errors());
                    print_r($error);
                }
                // insert file_upload
                $filesAll = array();
                $article_dataFiles=[];
                
                if(isset($_FILES['userfileupload0'])){
                $filesupload_length = sizeof($_FILES)-1;
                for($x = 0; $x < $filesupload_length; $x++) {
                    // Set New FileName
                    $ranSTR = date('dmYHis').substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,10))), 1, 10);
                    $nameF = substr(strrev($_FILES['userfileupload'.$x]['name']), 0, strrpos(strrev($_FILES['userfileupload'.$x]['name']),"."));
                    $typeF = strrev($nameF);
                    $_FILES['userfileupload'.$x]['name'] = $ranSTR.'.'.$typeF;
                    // End Set FileName
                    $configFiles = array(
                        'upload_path'      => './../client/src/assets/Files_Upload/',
                        'allowed_types' => '*',
                        'max_size'      => '0',
                    );
                    $this->load->library('upload', $configFiles,'files_upload');
                    $this->files_upload->initialize($configFiles);
                    if ($this->files_upload->do_upload('userfileupload'.$x)){
                        $data = array('upload_data' => $this->files_upload->data());
                        array_push($filesAll,array(
                            'f_title'=>$_FILES['userfileupload'.$x]['name'],
                            'f_key'=>$key_link,
                            'f_create_date'=>$this->Check__model->date_time_now()
                        ));
                    }else{
                        $error = array('error' => $this->upload->display_errors());
                        print_r($error);
                    }
                } 
                if(sizeof($filesAll) == $filesupload_length){
                    $dataFiles = $this->Files_Upload_model->insert_files_upload($filesAll);
                    array_push($article_dataFiles,array('article'=>$article,'files'=>$dataFiles));
                }else{
                    echo 'Error !!!';
                }
            }else{
                array_push($article_dataFiles,array('article'=>$article,'files'=>null));

            }
                echo json_encode($article_dataFiles);

        }
        // update Article
        public function update_article(){
            // check status for insert
            $creator = json_decode($this->input->post('creator'));
            if($creator==null || $creator==''){
                echo 'fail';
                exit;
            }
            $creatorID  = $this->Check__model->chk_token($creator);
            $statusUser = $this->Check__model->chk_status($creatorID);
            if( $statusUser != 'admin' ){
                echo 'fail';
                exit ;
            }
            //update
            $article = (array)json_decode($this->input->post('article'));
            if(isset($_FILES['userfile'])){
                $ranSTR = date('dmYHis').substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,10))), 1, 10);
                $nameF = substr(strrev($_FILES['userfile']['name']), 0, strrpos(strrev($_FILES['userfile']['name']),"."));
                $typeF = strrev($nameF);
                $_FILES['userfile']['name'] = $ranSTR.'.'.$typeF;
                $config = array(
                    'upload_path'      => './../client/src/assets/Article/',
                    'allowed_types' => '*',
                    'max_size'      => '0',
                );
                $this->load->library('upload', $config);
                if ($this->upload->do_upload('userfile')){
                    $data = array('upload_data' => $this->upload->data());
                    $article['a_image'] = $_FILES['userfile']['name'];
                }else{
                    $error = array('error' => $this->upload->display_errors());
                    print_r($error);
                    exit;
                }
            }
                $articleEditID['a_id'] = $article['a_id'];
                unset($article['a_id']); 
                $article['a_update_date'] = $this->Check__model->date_time_now();
                $thisUpdate = $this->article_model->update_article($article,$articleEditID);
                if($thisUpdate == true){
                    $article['a_id'] = $articleEditID['a_id'];
                    echo json_encode($article);
                }
        }









        
    }

?>