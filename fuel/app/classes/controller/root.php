<?php

class Controller_Root extends Controller {

    public function action_index() {
        $accounts = Config::get('cdn');
        $data = array(
            'accounts' => $accounts,
        );
        return Response::forge(View::forge('root/index', $data));
    }

    public function action_purge() {
        $service = $this->param('service');
        $account = $this->param('account');
        $data = array(
            'service' => $service,
            'account' => $account,
        );
        return Response::forge(Presenter::forge('root/purge'));
    }

    public function action_404() {
        return Response::forge(Presenter::forge('root/404'), 404);
    }

}
