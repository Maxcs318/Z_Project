<?php header('Access-Control-Allow-Origin: *'); ?>
<?php
    class News extends CI_Controller
    {
        // public $JSON_DATA;
        public function __construct()
        {
            parent::__construct();
            $this->load->model("news_model");
            $this->load->model('../../Check_/models/Check__model');
            $this->load->model('../../Files_Upload/models/Files_Upload_model');
            $this->output->set_content_type("application/json", 'utf-8');
            // $this->output->set_header("Access-Control-Allow-Origin: *");
            $this->output->set_header("Access-Control-Allow-Methods: GET, POST , OPTIONS");
            $this->output->set_header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");

            $this->load->helper("URL", "DATE", "URI", "FORM");//
            $this->load->library('form_validation');//

        }
        // get all News
        public function get_all_news()
        {
            echo $this->news_model->get_all_news();
        }
        // insert News
        public function insert_news()
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
            $key_link = 'N'.date('dmYHis').substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,10))), 1, 10);
            // insert image & news
            $news = (array)json_decode($this->input->post('news'));
                $ranSTR = date('dmYHis').substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,10))), 1, 10);
                $nameF = substr(strrev($_FILES['userfile']['name']), 0, strrpos(strrev($_FILES['userfile']['name']),"."));
                $typeF = strrev($nameF);
                $_FILES['userfile']['name'] = $ranSTR.'.'.$typeF;
                $config = array(
                    'upload_path'   => './../client/src/assets/News/',
                    'allowed_types' => '*',
                    'max_size'      => '0',
                );
                $this->load->library('upload', $config,'file_image');
                $this->file_image->initialize($config);
                if ($this->file_image->do_upload('userfile')){
                    $data = array('upload_data' => $this->file_image->data());
                    $news['n_image'] = $_FILES['userfile']['name'];
                    $news['n_file_key'] = $key_link;
                    $news['n_create_date'] = $this->Check__model->date_time_now();
                    $thisID = $this->news_model->insert_news($news);
                    $news['n_id']=$thisID;
                }else{
                    $error = array('error' => $this->upload->display_errors());
                    print_r($error);
                }
                // insert file_upload
                $filesAll = array();
                $news_dataFiles=[];

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
                            'upload_path'   => './../client/src/assets/Files_Upload/',
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
                        array_push($news_dataFiles,array('news'=>$news,'files'=>$dataFiles));
                    }else{
                        echo 'Error !!!';
                    }
                }else{
                    array_push($news_dataFiles,array('news'=>$news,'files'=>null));

                }
                echo json_encode($news_dataFiles);


        }
        // update News
        public function update_news(){
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
            $news = (array)json_decode($this->input->post('news'));
            if(isset($_FILES['userfile'])){
                $ranSTR = date('dmYHis').substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,10))), 1, 10);
                $nameF = substr(strrev($_FILES['userfile']['name']), 0, strrpos(strrev($_FILES['userfile']['name']),"."));
                $typeF = strrev($nameF);
                $_FILES['userfile']['name'] = $ranSTR.'.'.$typeF;
                $config = array(
                    'upload_path'      => './../client/src/assets/News/',
                    'allowed_types' => '*',
                    'max_size'      => '0',
                );
                $this->load->library('upload', $config);
                if ($this->upload->do_upload('userfile')){
                    $data = array('upload_data' => $this->upload->data());
                    $news['n_image'] = $_FILES['userfile']['name'];
                }else{
                    $error = array('error' => $this->upload->display_errors());
                    print_r($error);
                    exit;
                }
            }
                $newsEditID['n_id'] = $news['n_id'];
                unset($news['n_id']); 
                $news['n_update_date'] = $this->Check__model->date_time_now();
                $thisUpdate = $this->news_model->update_news($news,$newsEditID);
                if($thisUpdate == true){
                    $news['n_id'] = $newsEditID['n_id'];
                    echo json_encode($news);
                }
        }









        
    }

?>