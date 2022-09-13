<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 


$this->import('
    mapas-container  mapas-breadcrumb entity-admins
    entity-terms share-links entity-files-list entity-links  entity-list entity-owner entity-related-agents entity-seals entity-header entity-gallery entity-gallery-video entity-social-media');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label'=> i::__('Projetos'), 'url' => $app->createUrl('panel', 'projects')],
    ['label'=> $entity->name, 'url' => $app->createUrl('project', 'single', [$entity->id])],
];
?>

<div class="main-app single">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity"></entity-header>

    <mapas-container class="single-1__content">
        <div class="divider"></div>
        
        <main>
            <div class="grid-12">
                <div class="col-12">
                        <h2>Descrição Detalhada</h2>
                        <p>{{entity.longDescription}}</p>
                </div>
                    
                <div class="col-12">
                    <entity-files-list :entity="entity" group="downloads" title="Arquivos para download"></entity-files-list>
                </div>

                <div v-if="entity" class="col-12">
                    <entity-gallery-video :entity="entity"></entity-gallery-video>
                </div>

                <div class="col-12">
                    <entity-gallery :entity="entity"></entity-gallery>
                </div>
                <div class="property col-12">
                    <button class="button button--primary button--md">Reinvindicar Propriedade</button>
                </div>
                
            </div>
        </main>
        
        <aside>
            <div class="grid-12">
                <div class="col-12">
                    <entity-social-media :entity="entity"></entity-social-media>
                </div>
                
                <div class="col-12">
                    <entity-seals :entity="entity" title="Verificações"></entity-seals>
                </div>
                
                <div class="col-12">
                    <entity-related-agents :entity="entity"  title="Agentes Relacionados"></entity-related-agents>  
                </div>

                <div class="col-12">
                    <entity-terms :entity="entity" taxonomy="tag" title="Tags"></entity-terms>
                </div>

                <div class="col-12">
                    <share-links title="Compartilhar" text="Veja este link:"></share-links>
                </div>
                
                <div class="col-12">
                    <entity-owner title="Publicado por" :entity="entity"></entity-links>
                </div>

                <div class="col-12">
                    <entity-admins :editable="false" :entity="entity"></entity-admins>
                </div>

                <div class="col-12">
                    <h4>Propriedades do Projeto</h4>
                </div>
                <div class="col-12">
                    <entity-list :entity="entity" title="Subprojetos" property-name="children" type="project"></entity-list>
                </div>
                <div class="col-12">
                    <entity-list :entity="entity" title="Oportunidades" property-name="opportunity" type="opportunity"></entity-list>
                </div>
            </div>
        </aside>
    </mapas-container>
</div>