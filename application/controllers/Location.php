<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Location extends CI_Controller {

    function __construct() {

        parent::__construct();
        // error_reporting(E_PARSE);
        $this->load->model('Md');
        $this->load->library('session');
        $this->load->library('encrypt');
        date_default_timezone_set("Africa/Nairobi");
        $this->load->library('helper');
    }

    public function index() {

        $this->load->view('home');
    }

    public function map() {

        $data['locations'] = array();

        $all = array();
        $query1 = $this->Md->query("select * from user");

        //var_dump($query1);
        foreach ($query1 as $v) {
            $resv = new stdClass();
            $resv->image = $v->image;
            $resv->username = $v->username;
            $query2 = $this->Md->query("select * from location where username ='" . $v->username . "' order by id desc LIMIT 1 ");
            $results = $query2;
            $resv->lat = "";
            $resv->lng = "";
            $resv->created = "";
            foreach ($results as $res) {
                $resv->lat = $res->lat;
                $resv->lng = $res->lng;
                $resv->created = $res->created;
            }
            array_push($all, $resv);
        }
        $data['locations'] = $all;

        $this->load->view('view-all', $data);
    }

    public function save() {
    
         $this->load->helper(array('form', 'url'));
      
        $username = $this->input->post('username');
        $lat = $this->input->post('lat');
        $long = $this->input->post('long');
        
     
         // $username = "Douglas";
            
         // $userid = "23";
          //$lat = "0.2207913";
          //$long = "32.7943488";
       
          
              $dist = 0;
              $distance = 0;
              $distancem =0;
         
        $created = date('Y-m-d H:i:s');
        if ($username != "") {
            $results = $this->Md->query("select * from location where username ='" . $username . "'");

            if (!$results) {

                $locate = array('username' => $username, 'userid' => "", 'distance' => "0", 'lat' => $lat, 'lng' => $long, 'created' => $created);
                $this->Md->save($locate, 'location');
                $b["distance"] = "submitted first one";
                echo json_encode($b);
                return;
            }
            $resulte = $this->Md->query("select * from location where username ='".$username."' ORDER BY id DESC LIMIT 0, 1 " );
            // $b["posted"] =  $results;             
             
            foreach ($resulte as $res) {

              $b["lat2"] = $res->lat;
              $b["lng2"] = $res->lng;
               $b["lat"] = $lat;
              $b["lng"] = $long;
                // distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
                $lat2 = $res->lat;
                $lng2 = $res->lng;
                
            }
             //  echo $b["distance"] = $this->distance(0.3419071 , 32.5944203 , 0.3419071 , 32.5944204 , "K") . "Km";
              if($lat != NULL && $long!=NULL) {
                  
                $dist = $this->distance( $lat2, $lng2,$lat, $long, "K");
                $distance = ($dist * 1000);
                $distancem = number_format($distance, 1);
                $b["distance"] = $distancem . "metres";
                      
               }
               
                /// echo json_encode($b);                 

                if (bccomp($lat, $lat2)==0 && bccomp($long,$lng2)==0) {
                    $b["distance"] = $distancem . "m same location";
                    echo json_encode($b);
                    return;
                } else if((int)$distancem <= 20) {                 

                        $b["distance"] = " too short " . (int)$distancem . "m";
                        echo json_encode($b);
                    } else {
                        $locate = array('username' => $username, 'userid' => "", 'distance' => (int)$distancem, 'lat' => $lat, 'lng' => $long, 'created' => $created);
                        $this->Md->save($locate, 'location');
                      

                        $b["distance"] = "submitted distance". (int)$distancem.'  first lat: '.$lat.'second lat:'.$lat2.' first long: '.$long.' second long:'.$lng2;
                        echo json_encode($b);
                          $lat= 0 ;$long= 0;
                    }              

        } else {

            $b["posted"] = "invalid user";
            echo json_encode($b);
        }
    }

    /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
    /* ::                                                                         : */
    /* ::  This routine calculates the distance between two points (given the     : */
    /* ::  latitude/longitude of those points). It is being used to calculate     : */
    /* ::  the distance between two locations using GeoDataSource(TM) Products    : */
    /* ::                                                                         : */
    /* ::  Definitions:                                                           : */
    /* ::    South latitudes are negative, east longitudes are positive           : */
    /* ::                                                                         : */
    /* ::  Passed to function:                                                    : */
    /* ::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  : */
    /* ::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  : */
    /* ::    unit = the unit you desire for results                               : */
    /* ::           where: 'M' is statute miles                                   : */
    /* ::                  'K' is kilometers (default)                            : */
    /* ::                  'N' is nautical miles                                  : */
    /* ::  Worldwide cities and other features databases with latitude longitude  : */
    /* ::  are available at http://www.geodatasource.com                          : */
    /* ::                                                                         : */
    /* ::  For enquiries, please contact sales@geodatasource.com                  : */
    /* ::                                                                         : */
    /* ::  Official Web site: http://www.geodatasource.com                        : */
    /* ::                                                                         : */
    /* ::         GeoDataSource.com (C) All Rights Reserved 2014                  : */
    /* ::                                                                         : */
    /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

    public function distance($lat1, $lon1, $lat2, $lon2, $unit) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

//echo distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
//echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
//echo distance(32.9697, -96.80322, 29.46786, -98.53506, "N") . " Nautical Miles<br>";

    public function delete() {

        $id = $this->uri->segment(3);

        $query = $this->Md->delete($id, 'metar');

        if ($this->db->affected_rows() > 0) {
            $msg = '<span style="color:red">Information Deleted Fields</span>';
            $this->session->set_flashdata('msg', $msg);
            redirect('/metar', 'refresh');
        } else {
            $msg = '<span style="color:red">action failed</span>';
            $this->session->set_flashdata('msg', $msg);
            redirect('/metar', 'refresh');
        }
    }

    public function check($metar) {
        $this->load->helper(array('form', 'url'));

        $metar = ($metar == "") ? $this->input->post('name') : $metar;
        //check($value,$field,$table)
        $get_result = $this->Md->check($metar, 'name', 'metar');

        if (!$get_result)
            echo '<span style="color:#f00"> name already in use. </span>';
        else
            echo '<span style="color:#0c0"> name not in use</span>';
    }

    public function check_email() {
        $this->load->helper(array('form', 'url'));

        $email = $this->input->post('email');
        //check($value,$field,$table)
        $get_result = $this->Md->check($email, 'email', 'metar');

        if (!$get_result)
            echo '<span style="color:#f00">email already in use. </span>';
        else
            echo '<span style="color:#0c0">email not in use</span>';
    }

}
