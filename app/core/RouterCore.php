<?php

namespace app\core;

class RouterCore{
    private $uri;
    private $method;
    private $getArr = [];

    public function __construct()
    {
        $this -> initialize();
        require_once('../../app/config/router.php');
        $this -> execute();
    }

    private function initialize()
    {
        $this -> method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        if(strpos($uri, '?'))
            $uri = mb_substr($uri, 0, strpos($uri, '?'));

        $ex = explode('/', $uri);

        $uri = implode('/', $this -> normalizerURI($ex));

        if (is_array($uri)) {
            for ($i = 0; $i < UNSET_URI_COUNT; $i++) {
                unset($uri[$i]);
            }
        } else {
            // Caso $uri não seja um array, trate essa situação
            // Talvez você precise convertê-la em um array ou ajustar a lógica
            $uri = explode('/', $uri); // Exemplo de como converter uma string para array
        }
        

        dd(array_values(array_filter($ex)));
        
        $this -> uri = $this -> normalizerURI($uri);
        if(DEBUG_URI)
            dd($this -> uri);
    }

    private function get($router, $call)
    {
        $this -> getArr[] = [
            'router' => $router,
            'call' => $call
        ];
    }

    private function post($router, $call)
    {
        $this -> getArr[] = [
            'router' => $router,
            'call' => $call
        ];
    }

    private function execute()
    {
        switch($this -> method){
            case 'GET':
                $this -> executeGet();
                break;
            case 'POST':
                $this -> executePost();
                break;
        }
    }

    private function executeGet()
    {
        foreach($this -> getArr as $get){
            $r = substr($get['router'], 1);

            if (substr($r, -1) == '/') {
                $r = substr($r, 0, -1);
            }

            if($r == $this -> uri){
                if(is_callable($get['call'])){
                    $get['call']();
                    break;
                } else{
                    $this -> executeController($get['call']);
                }
            }
        }
    }

    private function executePost()
    {
        foreach ($this->getArr as $get) {
            $r = substr($get['router'], 1);

            if (substr($r, -1) == '/') {
                $r = substr($r, 0, -1);
            }

            if ($r == $this->uri) {
                if (is_callable($get['call'])) {
                    $get['call']();
                    return;
                }

                $this->executeController($get['call']);
            }
        }
    }
    private function executeController($get)
    {
        $ex = explode('@', $get);
        if (!isset($ex[0]) || !isset($ex[1])) {
            (new \app\controller\MessageController)->message('Dados inválidos', 'Controller ou método não encontrado: ' . $get, 404);
            return;
        }

        $cont = 'app\\controller\\' . $ex[0];
        if (!class_exists($cont)) {
            (new \app\controller\MessageController)->message('Dados inválidos', 'Controller não encontrada: ' . $get, 404);
            return;
        }


        if (!method_exists($cont, $ex[1])) {
            (new \app\controller\MessageController)->message('Dados inválidos', 'Método não encontrado: ' . $get, 404);
            return;
        }

        call_user_func_array([
            new $cont,
            $ex[1]
        ], []);
    }

    private function normalizerURI($arr)
    {
        return array_values(array_filter($arr));
    }
    
}


?>