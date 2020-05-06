<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {


  public function __construct() // function untuk mencegah user yg belum login
  {
    parent::__construct();
    is_logged_in();
    // if (!$this->session->userdata('email')) {
    //   redirect('auth');
    // }
  }

  public function index(){
    $data['title'] = "My Profile";
    $data['user'] = $this->db->get_where('user', ['email'=> $this->session->userdata('email')])->row_array();
    // echo "selamat datang " . $data['user']['name'];

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('templates/topbar', $data);
    $this->load->view('user/index', $data);
    $this->load->view('templates/footer');
  }

  public function edit(){
    $data['title'] = "Edit Profile";
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
    // echo "selamat datang " . $data['user']['name'];

    $this->form_validation->set_rules('name', 'Fullname', 'required|trim');

    if($this->form_validation->run() == false){
      $this->load->view('templates/header', $data);
      $this->load->view('templates/sidebar', $data);
      $this->load->view('templates/topbar', $data);
      $this->load->view('user/edit', $data);
      $this->load->view('templates/footer');
    }else{
      $name = $this->input->post('name');
      $email = $this->input->post('email');

      // cek jika tidak ada gambar yang akan diupload
      $upload_image = $_FILES['image']['name'];
      // var_dump($image);die;

      if($upload_image){ // jika ada file yang diupload
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size']     = '12048';  // max 2 mb
        $config['upload_path'] = './assets/img/profile'; // folder ini di mulai dari root nya 

        $this->load->library('upload', $config); // jalankan library nya

        if($this->upload->do_upload('image')){ // jika upload dari input yang name nya image berhasil
          $old_image = $data['user']['image']; // ambil gambar lama
          if($old_image != 'default.png'){
            unlink(FCPATH . 'assets/img/profile/' . $old_image); // FCPATH untuk mengetahui file ke file name nya
          }

          $new_image = $this->upload->data('file_name'); //data ini berisi file yang sudah diupload beserta semua informasinya
          $this->db->set('image', $new_image); // menambahkan set yang baru
        }else{
          $this->upload->display_errors();
        }


      }

      $this->db->set('name', $name);
      $this->db->where('email', $email);
      $this->db->update('user');

      $this->session->set_flashdata('message', '<div class="alert alert-success text-center" role="alert">
      Your profile has been updated
      </div>');
      redirect('user');
    }

  }


  public function changepassword(){
    $data['title'] = "Change Password";
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
    // echo "selamat datang " . $data['user']['name'];

    $this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');
    $this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[3]|matches[new_password2]');
    $this->form_validation->set_rules('new_password2', 'Confirm new Password', 'required|trim|min_length[3]|matches[new_password1]');

    if($this->form_validation->run() == false){
      $this->load->view('templates/header', $data);
      $this->load->view('templates/sidebar', $data);
      $this->load->view('templates/topbar', $data);
      $this->load->view('user/changepassword', $data);
      $this->load->view('templates/footer');
    }else{
      $current_password = $this->input->post('current_password');
      $new_password = $this->input->post('new_password1');

      if(!password_verify($current_password, $data['user']['password'])){ // cek password inputan dengan password di db
        $this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">
        Wrong Current Password!
        </div>');
        redirect('user/changepassword');
      }else{
        
        if($current_password == $new_password){
          $this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">
          New Password cannot be the same as current password!
          </div>');
          redirect('user/changepassword');
        }else{
          // password sudah ok
          $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
          $this->db->set('password', $password_hash);
          $this->db->where('email', $this->session->userdata('email'));
          $this->db->update('user');

          $this->session->set_flashdata('message', '<div class="alert alert-success text-center" role="alert">
          Password Changed!
          </div>');
          redirect('user/changepassword');
        }

      }

    }

  }

}