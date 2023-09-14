<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entity;

/**
 * @property-read string $permissionCacheClassName
 * @property-read string[] $permissionsList
 */
trait EntityPermissionCache {
    public $__skipQueuingPCacheRecreation = false;

    private $__enabled = true;

    public static function usesPermissionCache() {
        return true;
    }

    static function getPermissionCacheClassName(){
        return self::getClassName() . 'PermissionCache';
    }

    function permissionCacheExists() {
        return $this->__enabled && !is_null($this->__permissionsCache) && count($this->__permissionsCache) > 0;
    }

    function _cachedCanUser($action, \MapasCulturais\UserInterface $user) {
        foreach ($this->__permissionsCache as $cache) {
            if ($cache->action === $action && $cache->userId === $user->id) {
                return true;
            }
        }

        return false;
    }
    
    function getPCacheObjectType(){
        $class_name = $this->getClassName();
        $metadata = App::i()->em->getClassMetadata($class_name);
        if($root_class = $metadata->rootEntityName){
            $class_name = $root_class;
        }
        
        return $class_name;
    }


    protected $permissionCacheEnabled = true;

    public function getPermissionCachePrefix() {
        $app = App::i();
        $prefix = $app->mscache->fetch("$this::permission-cache-prefix");

        if(!$prefix) {
            $prefix = $this->renewPermissionCachePrefix();
        }

        return $prefix;
    }

    public function renewPermissionCachePrefix() {
        $app = App::i();
        $prefix = uniqid();
        $app->mscache->delete("$this::permission-cache-prefix");
        $app->mscache->save("$this::permission-cache-prefix", $prefix);
        return $prefix;
    }

    public function getPermissionCacheKey($user, $action) {
        $prefix = $this->getPermissionCachePrefix();
        return "$prefix::{$this->getHookClassPath()}:{$this->id}::User:{$user->id}::$action";
    }
    
    function createPermissionsCacheForUsers(array $users = null, $flush = false, $delete_old = true) {
        $this->permissionCacheEnabled = false;
        $this->renewPermissionCachePrefix();

        $app = App::i();
        if($this->getEntityState() !== 2){
            $this->refresh();
        }
        
        if(!$this->id){
            return;
        }

        if($app->config['app.log.pcache']){
            $start_time = microtime(true);
            $app->log->debug("RECREATING pcache FOR $this");
        }
        
        if(is_null($users)){
            if($this->usesAgentRelation()){
                $users = $this->getUsersWithControl();
            } else if($this->owner) {
                $users = $this->owner->getUsersWithControl();
            } else {
                $users = [$this->getOwnerUser()];
            }
            
            if(method_exists($this, 'getExtraPermissionCacheUsers')){
                $users = array_merge($users, $this->getExtraPermissionCacheUsers());
            }

            if($roles = $app->repo("Role")->findAll()){
                foreach($roles as $role){
                    $users[] = $role->user;
                }
            }
        }
        $app->applyHookBoundTo($this, "{$this->hookPrefix}.permissionCacheUsers", [&$users]);

            if($delete_old && $users){
                $this->deletePermissionsCache();
            }
        }
        
        $conn = $app->em->getConnection();
        $class_name = $this->getPCacheObjectType();
        $permissions = $this->getPermissionsList();
        $this->__enabled = false;
        $isPrivateEntity = $class_name::isPrivateEntity();
        $hasCanUserViewMethod = method_exists($this, 'canUserView');
        $isStatusNotDraft = ($this->status > Entity::STATUS_DRAFT);

        $already_created_users = [];
        $users = array_unique($users);

        foreach ($users as $user) {
            if (is_null($user)) {
                continue;
            }

            if($user->is('guest')){
                continue;
            }

            if(isset($already_created_users["$user"])){
                continue;
            }

            $already_created_users["$user"] = true;

            if($user->is('admin', $this->_subsiteId)){
                continue;
            }

            $allowed_permissions = [];

            foreach ($permissions as $permission) {
                if($permission === 'view' && $isStatusNotDraft && !$isPrivateEntity && !$hasCanUserViewMethod) {
                    continue;
                }

                if ($this->canUser($permission, $user)) {
                    $allowed_permissions[] = $permission;
                    $conn->insert('pcache', [
                        'user_id' => $user->id,
                        'action' => $permission,
                        'object_type' => $class_name,
                        'object_id' => $this->id,
                        'create_timestamp' => 'now()'
                    ]);
                }
            }

            if($app->config['app.log.pcache.users'] && $allowed_permissions){
                $allowed_permissions = implode(',', $allowed_permissions);
                $app->log->debug(' PCACHE >> ' . str_replace('MapasCulturais\\Entities\\', '', "{$this}:{$user}($allowed_permissions)"));
            }
        }

        if($app->config['app.log.pcache']){
            $end_time = microtime(true);
            $total_time = number_format($end_time - $start_time, 1);

            $app->log->info("pcache FOR $this CREATED IN {$total_time} seconds\n\n");
        }
        
        $this->__enabled = true;
    }
    
    function deletePermissionsCache(){
        $app = App::i();
        $conn = $app->em->getConnection();
        $class_name = $this->getPCacheObjectType();
        if(!$this->id){
            return;
        }
        $conn->executeQuery("DELETE FROM pcache WHERE object_type = '{$class_name}' AND object_id = {$this->id}");
    }
       
    function enqueueToPCacheRecreation($skip_extra = false){
        $app = App::i();
        if($app->isEntityEnqueuedToPCacheRecreation($this) || $this->__skipQueuingPCacheRecreation){
            return false;
        }
        
        $app->enqueueEntityToPCacheRecreation($this);

        return true;
    }


    function recreatePermissionCache($users = null, $path = ''){
        $app = App::i();

        $path .= str_replace('MapasCulturais\\Entities\\', '', "$this");

        if($app->config['app.log.pcache']){
            $app->log->debug($path);
        }

        if($app->isEntityPermissionCacheRecreated($this)){
            return false;
        }

        $self = $this;

        $app->setEntityPermissionCacheAsRecreated($self);



        $conn = $app->em->getConnection();
        $conn->beginTransaction();

        try {
            $self->createPermissionsCacheForUsers($users);
            $conn->commit();
        } catch (\Exception $e ){
            $conn->rollBack();
            throw $e;
        }

        if($self instanceof \MapasCulturais\Entities\User) {
            return true;
        }

        $class_relations = $app->em->getClassMetadata($self->getClassName())->getAssociationMappings();
        
        foreach($class_relations as $prop => $def){
            $rel_class = $def['targetEntity'];
            if($def['type'] == 4 && !$def['isOwningSide'] && $rel_class::usesPermissionCache()){
                $total = count($self->$prop);
                foreach($self->$prop as $i => $entity){
                    $entity->recreatePermissionCache($users, "{$path}->{$prop}({$i}/$total):");
                }
            }
            
        }

        
        if(method_exists($self, 'getExtraEntitiesToRecreatePermissionCache')){
            $entities = $self->getExtraEntitiesToRecreatePermissionCache();
            $total = count($entities);
            foreach($entities as $i => $entity){
                $i++;
                $entity->recreatePermissionCache($users, "{$path}->extra({$i}/$total):");
            }
        }
    }
}
