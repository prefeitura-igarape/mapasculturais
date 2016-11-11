<?php

namespace SealModel1;

use SealModelTab;



class Plugin extends SealModelTab\SealModelTemplatePlugin
{
    function getModelName(){
        return ['label'=> 'Modelo Mapas 1', 'name' => 'SealModel1'];
    }

    function getCssFileName(){
        return 'model-tab-1.css';
    }

    function getBackgroundImage(){
        return 'modelo_certificado_01.jpg';
    }
}