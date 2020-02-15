<?php

namespace Rupalipshinde\Template;

//
use Illuminate\Support\Facades\Route;

class Template {

    public function templateUsingEvent($event) {
        return (new Http\Controllers\TemplateController)->findTemplateUsingEvent($event);
    }

    public static function routes() {
        Route::get('/get-templates', '\Rupalipshinde\Template\Http\Controllers\TemplateController@forTemplate');
        Route::get('/get-template/{template_id}', '\Rupalipshinde\Template\Http\Controllers\TemplateController@forSelectedTemplate');
        Route::get('/get-template-using-event/{event}', ['as' => 'get-template-using-event', 'uses' => '\Rupalipshinde\Template\Http\Controllers\TemplateController@findTemplateUsingEvent']);
        Route::get('/get-template-using-language/{language}/{event}', ['as' => 'get-template-using-language', 'uses' => '\Rupalipshinde\Template\Http\Controllers\TemplateController@findTemplateUsingLanguage']);
        Route::post('/store-template', '\Rupalipshinde\Template\Http\Controllers\TemplateController@store');
        Route::put('/update-template/{template_id}', '\Rupalipshinde\Template\Http\Controllers\TemplateController@update');
         Route::post('/update-template-status/{status}', '\Rupalipshinde\Template\Http\Controllers\TemplateController@updateTemplateStatus');
        Route::delete('/delete-template/{template_id}', '\Rupalipshinde\Template\Http\Controllers\TemplateController@destroy');
    }

}
