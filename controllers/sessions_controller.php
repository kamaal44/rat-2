<?php

class SessionsController extends Application {

  protected $requireLoggedIn = array('remove');
  protected $requireLoggedOut = array('add');

  function add() {

    if (isset($_POST['email']) && isset($_POST['password'])) {

      // User trying to sign up but app not configured, error out
      if (Admin::count_users() == 0) {

        Application::flash('error', $this->config->name . ' is not yet configured properly.
          <br />Please contact the creator of this app.');
        $this->loadView('items/index');
        exit();

      }

      $user = User::get_by_email($_POST['email']);

      if ($user != NULL && $user->authenticate($_POST['password'], $this->config->encryption_salt) == TRUE) {

        // Get redirected
        if (isset($this->uri['params']['redirect_to'])) {
          $redirect_url = $this->uri['params']['redirect_to'];
        } else {
          $redirect_url = $this->config->url;
        }

        // Go forth
        header('Location: ' . $redirect_url);
        exit();

      } else {

        Application::flash('error', 'Something isn\'t quite right. Please try again...');
        $email = $_POST['email'];

      }

    }

    if ( ! isset($_SESSION['user_id'])) {

      if (isset($email)) {
        $this->loadView('sessions/add', array('email' => $email));
      } else {
        $this->loadView('sessions/add');
      }

    } else {

      Application::flash('error', 'You are already logged in! ' . $this->get_link_to('Click here', 'sessions', 'remove') . ' to logout.');
      $this->loadView();

    }

  }

  function remove() {

    $user = User::get_by_id($_SESSION['user_id']);

    if ($user->deauthenticate() == TRUE) {

      Application::flash('info', 'You are now logged out.');
      Application::redirect_to('items');

    } else {

      Application::flash('info', 'Nothing to see here.');
      $this->loadView();

    }

  }

}
