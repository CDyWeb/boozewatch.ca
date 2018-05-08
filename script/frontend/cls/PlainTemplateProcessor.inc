<?

class PlainTemplateProcessor extends DefaultTemplateProcessor {
    
    protected function pre_process(Router $router, $template, Output $output) {
    
    var_dump($this->router);
    
        $this->template=
<<<HTML

<html>
    <head>
    </head>
    <body>
    aaa
    </body>
</html>

HTML;
    
    }
    
}

//end