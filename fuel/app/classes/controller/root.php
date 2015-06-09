<?php

class Controller_Root extends Controller {

    public function action_index() {
        
        return Response::forge(View::forge('root/index'));
    }
    
    public function action_404() {
        return Response::forge(Presenter::forge('root/404'), 404);
    }

}
