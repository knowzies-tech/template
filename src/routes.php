<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


Route::get('/get-templates', '\Rupalipshinde\Template\Http\Controllers\TemplateController@forTemplate');
Route::get('/get-template/{template_id}', '\Rupalipshinde\Template\Http\Controllers\TemplateController@forSelectedTemplate');
Route::get('/get-template-using-event/{event}', ['as' => 'get-template-using-event','uses'=>'\Rupalipshinde\Template\Http\Controllers\TemplateController@findTemplateUsingEvent']);

Route::get('/get-template-using-language/{language}/{event}', ['as' => 'get-template-using-language','uses'=>'\Rupalipshinde\Template\Http\Controllers\TemplateController@findTemplateUsingLanguage']);
Route::post('/store-template', '\Rupalipshinde\Template\Http\Controllers\TemplateController@store');
Route::put('/update-template/{template_id}', '\Rupalipshinde\Template\Http\Controllers\TemplateController@update');
Route::delete('/delete-template/{template_id}', '\Rupalipshinde\Template\Http\Controllers\TemplateController@destroy');
 
Route::post('/update-template-status/{template_id}/{status}', '\Rupalipshinde\Template\Http\Controllers\TemplateController@updateTemplateStatus');
