<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

  public function __construct()
  {
    parent::__construct(); // untuk memanggil construct CI
    $this->load->library('form_validation');
  }

  public  function index(){
    $data['title'] = "Login Page";
    $this->load->view('templates/auth_header', $data);
    $this->load->view('auth/login');
    $this->load->view('templates/auth_footer');
  }
  
  public function registration(){

    $this->form_validation->set_rules('name', 'Name', 'required|trim');
    $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
      'is_unique' => 'this user has already registered'
    ]); // unique untuk cek sudah ada belum di database
    $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
      'matches' => 'password dont match',
      'min_length' => 'password too short'
    ]); // min panjang 3, agar cocok dengan password 2
    $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]'); // min panjang 3, agar cocok dengan password 2

    if($this->form_validation->run() == false){
      $data['title'] = "WPU - Registration";
      $this->load->view('templates/auth_header', $data);
      $this->load->view('auth/registration');
      $this->load->view('templates/auth_footer');
    }else{
      // echo "data berhasil ditambahkan";
      $data = [
        'name' => htmlspecialchars($this->input->post('name'), true),
        'email' => htmlspecialchars($this->input->post('email'), true),
        'image' => 'default.jpg',
        'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
        'role_id' => 2,
        'is_active' => 1,
        'date_created' => time()
      ];

      // insert ke db
      $this->db->insert('user', $data);
      $this->session->set_flashdata('message', '<div class="alert alert-success text-center" role="alert">
      Congratulations, your account has been created, <br> please login!
      </div>');
      redirect('auth');
    }
  }






}
