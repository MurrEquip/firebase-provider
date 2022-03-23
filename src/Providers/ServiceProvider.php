<?php

namespace Shelton\Firebase\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Kreait\Firebase;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/firebase.php', "firebase");

        $this->registerFactory();
        $this->registerAuth();
        $this->registerDatabase();
        $this->registerDynamicLinks();
        $this->registerFirestore();
        $this->registerMessaging();
        $this->registerRemoteConfig();
        $this->registerStorage();
    }

    public function registerStorage()
    {
        $this->app->singleton(Firebase\Storage::class, function ($app) {
            return $app->make(Firebase\Factory::class)->createStorage();
        });
        $this->app->alias(Firebase\Storage::class, 'firebase.storage');
    }

    public function registerRemoteConfig()
    {
        $this->app->singleton(Firebase\RemoteConfig::class, function ($app) {
            return $app->make(Firebase\Factory::class)->createRemoteConfig();
        });
        $this->app->alias(Firebase\RemoteConfig::class, 'firebase.remote_config');
    }

    public function registerMessaging()
    {
        $this->app->singleton(Firebase\Messaging::class, function ($app) {
            return $app->make(Firebase\Factory::class)->createMessaging();
        });
        $this->app->alias(Firebase\Messaging::class, 'firebase.messaging');
    }

    public function registerFirestore()
    {
        $this->app->singleton(Firebase\Firestore::class, function ($app) {
            return $app->make(Firebase\Factory::class)->createFirestore();
        });
        $this->app->alias(Firebase\Firestore::class, 'firestore');
    }

    public function registerDynamicLinks()
    {
        $this->app->singleton(Firebase\DynamicLinks::class, function ($app) {
            $defaultDynamicLinksDomain = $app->make('config')['firebase']['dynamic_links']['default_domain'] ?? null;

            return $app->make(Firebase\Factory::class)->createDynamicLinksService($defaultDynamicLinksDomain);
        });
        $this->app->alias(Firebase\DynamicLinks::class, 'dynamic_links');
    }

    public function registerDatabase()
    {
        $this->app->singleton(Firebase\Database::class, function ($app) {
            return $app->make(Firebase\Factory::class)->createDatabase();
        });
        $this->app->alias(Firebase\Database::class, 'firebase.database');
    }

    public function registerAuth()
    {
        $this->app->singleton(Firebase\Auth::class, function ($app) {
            return $app->make(Firebase\Factory::class)->createAuth();
        });
        $this->app->alias(Firebase\Auth::class, 'firebase.auth');
    }

    public function registerFactory()
    {
        $this->app->singleton(Firebase\Factory::class, function ($app) {
            $factory = new Firebase\Factory();
            $config = $app->make('config');
            $credentialsFile = $config->get('firebase.credentials.file', '');
            $enableAutoDiscovery = $config->get('firebase.credentials.auto_discover', true);
            $databaseUrl = $config->get('firebase.database.url');
            $defaultStorageBucket = $config->get('firebase.storage.default_bucket');
            $cacheStore = $config->get('firebase.cache_store');

            if (! $credentialsFile || ! file_exists($credentialsFile)) {
                throw new BindingResolutionException("The credential file '{$credentialsFile}' could not be found");
            }

            $factory = $factory->withServiceAccount($credentialsFile);
            if (! $enableAutoDiscovery) {
                $factory = $factory->withDisabledAutoDiscovery();
            }

            if ($databaseUrl) {
                $factory = $factory->withDatabaseUri($databaseUrl);
            }

            if ($defaultStorageBucket) {
                $factory = $factory->withDefaultStorageBucket($defaultStorageBucket);
            }

            if ($cacheStore) {
                $factory = $factory->withVerifierCache($app->make('cache')->store($cacheStore));
            }

            return $factory;
        });
    }
}
