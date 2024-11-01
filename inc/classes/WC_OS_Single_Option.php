<?php


class WC_OS_Single_Option
{

    private $main_option_name;
    private $main_option_data;


    public function __construct($main_option_name)
    {
        $this->main_option_name = $main_option_name;
        $this->main_option_data = get_option($this->main_option_name, array());
    }

    public function update_main_option(){

        return update_option($this->main_option_name, $this->main_option_data);

    }


    public function get_option($option_name, $default = false){

        if(array_key_exists($option_name, $this->main_option_data)){
            return $this->main_option_data[$option_name];
        }else{
            return $default;
        }

    }



    public function update_option($option_name, $option_value){

            $this->main_option_data[$option_name] = $option_value;
            return $this->update_main_option();

    }



    public function delete_option($option_name){

        if(array_key_exists($option_name, $this->main_option_data)){

            unset($this->main_option_data[$option_name]);
            return $this->update_main_option();


        }else{
            return false;
        }

    }



    public function get_main_option_data(){

        return $this->main_option_data;
    }

    public function delete_main_option_data(){

        $this->main_option_data = array();
        $this->update_main_option();
    }




}