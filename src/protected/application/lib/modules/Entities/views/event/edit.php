<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
        confirm-before-exit
        entity-actions
        entity-admins
        entity-cover
        entity-field
        entity-files-list
        entity-gallery
        entity-gallery-video
        entity-header
        entity-links
        entity-link-project
        entity-occurrence-list
        entity-owner
        entity-profile
        entity-related-agents
        entity-social-media
        entity-terms
        event-info
        mc-container
        mc-card
        mc-breadcrumb
        mc-tag-list
');

$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Meus Eventos'), 'url' => $app->createUrl('panel', 'events')],
    ['label' => $entity->name, 'url' => $app->createUrl('event', 'edit', [$entity->id])],
];
?>

<div class="main-app">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>
    <mc-container>
        <mc-card class="feature">
            <template #title>
                <label><?php i::_e("Informações de Apresentação") ?></label>
                <p><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários") ?></p>
            </template>
            <template #content>
                <div class="left">
                    <div class="grid-12 v-center">
                        <entity-cover :entity="entity" classes="col-12"></entity-cover>
                        <div class="col-3 sm:col-12">
                            <entity-profile :entity="entity"></entity-profile>
                        </div>
                        <div class="col-9 sm:col-12">
                            <div class="grid-12">
                                <entity-field :entity="entity" classes="col-12" label="Nome do Evento" prop="name"></entity-field>
                                <entity-field :entity="entity" classes="col-12" label="Subtítulo do evento" prop="subTitle"></entity-field>
                            </div>
                        </div>
                        <entity-field :entity="entity" classes="col-12" prop="shortDescription"></entity-field>
                        <entity-field :entity="entity" classes="col-12" label="Link para página ou site do evento" prop="site"></entity-field>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="right">
                    <div class="grid-12">
                            <entity-link-project :entity="entity" type="project" classes="col-12" label="<?php i::esc_attr_e('Vincular a um projeto')?>"></entity-link-project>
                        <entity-field :entity="entity" classes="col-12" label="Classificação etária" prop="classificacaoEtaria"></entity-field>
                        <entity-terms :entity="entity" classes="col-12" taxonomy="linguagem" editable title="Linguagens culturais"></entity-terms>
                        <entity-social-media :entity="entity" classes="col-12" editable></entity-social-media>
                    </div>
                </div>
            </template>
        </mc-card>
        <main>
            <mc-card>
                <template #content>
                    <div class="grid-12">
                        <div class="col-12">
                            <entity-occurrence-list :entity="entity" editable></entity-occurrence-list>
                        </div>
                    </div>
                </template>
            </mc-card>
            <mc-card>
                <template #title>
                    <label><?php i::_e("Informações sobre o evento"); ?></label>
                </template>
                <template #content>
                    <div class="grid-12">
                        <entity-field :entity="entity" classes="col-12" label="Total de público" prop="event_attendance"></entity-field>
                        <entity-field :entity="entity" classes="col-12" label="Telefone para informações sobre o evento" prop="telefonePublico"></entity-field>
                        <entity-field :entity="entity" classes="col-12" label="Informações sobre a inscrição" prop="registrationInfo"></entity-field>
                    </div>
                </template>
            </mc-card>
            <mc-card>
                <template #title>
                    <event-info :entity="entity" editable></event-info>
                </template>
                <template #content>
                </template>
            </mc-card>
            <mc-card>
                <template #title>
                    <label><?php i::_e("Informações públicas do Evento"); ?></label>
                    <p class="info-event"><?php i::_e("Este é um espaço para você apresentar melhor o seu Evento. Adicione documentos, links, vídeos e imagens."); ?></p>
                </template>
                <template #content>
                    <div class="grid-12">
                        <entity-field :entity="entity" classes="col-12 long-description" prop="longDescription" label="<?php i::esc_attr_e('Apresentação'); ?>"></entity-field>
                        <entity-files-list :entity="entity" classes="col-12" group="downloads" title="<?= i::esc_attr_e('Adicionar arquivos para download') ?>" editable></entity-files-list>
                        <entity-links :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Adicionar links'); ?>" editable></entity-links>
                        <entity-gallery-video :entity="entity" classes="col-12" title="<?= i::esc_attr_e('Adicionar vídeos') ?>" editable></entity-gallery-video>
                        <entity-gallery :entity="entity" classes="col-12" title="<?= i::esc_attr_e('Adicionar fotos na galeria') ?>" editable></entity-gallery>
                    </div>
                </template>
            </mc-card>
        </main>
        <aside>
            <mc-card>
                <template #content>
                    <div class="grid-12">
                        <entity-admins :entity="entity" classes="col-12" editable></entity-admins>
                        <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="Tags" editable></entity-terms>
                        <entity-related-agents :entity="entity" classes="col-12" editable></entity-related-agents>
                        <entity-owner :entity="entity" classes="col-12" title="Publicado por" editable></entity-owner>
                    </div>
                </template>
            </mc-card>
        </aside>
    </mc-container>
    <entity-actions :entity="entity" editable></entity-actions>
</div>
<confirm-before-exit :entity="entity"></confirm-before-exit>
