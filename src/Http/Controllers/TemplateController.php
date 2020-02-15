<?php

namespace Rupalipshinde\Template\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rupalipshinde\Template\TemplateModel;
use Rupalipshinde\Template\Http\Resources\Template as TemplateResource;
use Rupalipshinde\Template\Http\Requests\StoreTemplateRequest as StoreTemplateRequest;
use Rupalipshinde\Template\Http\Requests\UpdateTemplateRequest as UpdateTemplateRequest;
use Illuminate\Foundation\Application;

class TemplateController {

    /**
     * The template repository instance.
     *
     * @var Rupalipshinde\Template\TemplateRepository;
     */
    protected $templates;

    /**
     * The validation factory implementation.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validation;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Get all of the templates for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forTemplate(Request $request) {
        $appLang = $this->app->config->get('app.locale') ? $this->app->config->get('app.locale') : $this->app->config->get('app.fallback_locale');
        return TemplateResource::collection(TemplateModel::search($request->filter)
                                ->where('language', $appLang)
                                ->when($request->sort_name != '', function($query) use ($request) {
                                    $query->orderBy($request->sort_name, $request->sort_dir);
                                })
                                ->orderBy('created_at', 'desc')
                                ->paginate($request->size, ['*'], 'pageNumber'));
    }

    /**
     * Get selected template .
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forSelectedTemplate($templateId) {
        return new TemplateResource(TemplateModel::findOrFail($templateId));
    }

    /**
     * Get template using event .
     *
     * @param  $event
     * @param $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findTemplateUsingEvent($event, $data, $link = '') {
        $appLang = $this->app->config->get('app.locale') ? $this->app->config->get('app.locale') : $this->app->config->get('app.fallback_locale');
        $templateData = new TemplateResource(TemplateModel::where('event', $event)
                        ->where('language', $appLang)
                        ->where('status', '1')
                        ->first());
        if ($templateData) {
            foreach ($data as $datakey => $value) {
                foreach (json_decode($templateData['placeholder']) as $key => $value) {
                    if ($key == $datakey) {
                        $templateData['description'] = str_replace("[" . $key . "]", $data[$datakey], $templateData['description']);
                        break;
                    }
                }
            }

            if ($link != '') {
                $templateData['description'] = str_replace("[PASSWORD_RESET_URL]", $link, $templateData['description']);
            }
        }
        return $templateData;
    }

    /**
     * Get template using language .
     *
     * @param  $event
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findTemplateUsingLanguage($language, $event) {
        return new TemplateResource(TemplateModel::where('language', $language)
                        ->where('event', $event)
                        ->first());
    }

    /**
     * Store a new template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \rupalipshinde\template\Template
     */
    public function store(StoreTemplateRequest $request) {
        $template = new TemplateModel();
        $template->title = $request->title;
        $template->subject = $request->subject;
        $template->description = $request->description;
        $template->language = $request->language;
        $template->placeholder = $request->placeholder;
        $template->event = $request->event;
        $template->status = $request->status;
        $template->save();
        return response(
                array(
            "message" => __('translations.created_msg', array('attribute' => trans('common.template'))),
            "status" => true,
                ), 201);
    }

    /**
     * Update the given template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $templateId
     * @return \Illuminate\Http\Response|\rupalipshinde\template\Template
     */
    public function update(UpdateTemplateRequest $request, $event) {
        $template = TemplateModel::where('event', $event)
                        ->where('language', $request->language)->first();

        if (!$template) {
            $template = new TemplateModel();
            $template->placeholder = $request->placeholder;
            $template->event = $event;
            $template->status = '1';
        }

        $template->title = $request->title;
        $template->subject = $request->subject;
        $template->description = $request->description;
        $template->language = $request->language;
        $template->save();
        return response(
                array(
            "message" => __('translations.updated_msg', array('attribute' => trans('translations.template'))),
            "status" => true,
                ), 200);
    }

    /**
     * Update the status.
     *
     * @param  int $string
     * @param  string  $templateId
     * @return \Illuminate\Http\Response|\rupalipshinde\template\Template
     */
    public function updateTemplateStatus(Request $request, $status) {
        if (!in_array($status, array('0', '1'))) {
            return response(
                    array(
                "message" => __('validation.in', array('attribute' => trans('translations.status'))),
                "status" => false,
                    ), 422);
        }
        $template = TemplateModel::where('event', $request->event)->update([
            'status' => $status,
        ]);
        return response(
                array(
            "message" => __('translations.updated_msg', array('attribute' => trans('translations.status'))),
            "status" => true,
                ), 200);
    }

    /**
     * Delete the given template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $templateId
     * @return \Illuminate\Http\Response
     */
    public function destroy($templateId) {
//        $template = $this->templates->findForTemplate($templateId);
//
//        if (!$template) {
//            return new Response('', 404);
//        }
//
//        $this->templates->delete($template);
//
//        return new Response('', Response::HTTP_NO_CONTENT);
    }

}
