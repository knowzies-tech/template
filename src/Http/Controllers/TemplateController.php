<?php

namespace Rupalipshinde\Template\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rupalipshinde\Template\TemplateModel;
use Rupalipshinde\Template\Http\Resources\Template as TemplateResource;
use Rupalipshinde\Template\Http\Requests\StoreTemplateRequest as StoreTemplateRequest;
use Rupalipshinde\Template\Http\Requests\UpdateTemplateRequest as UpdateTemplateRequest;
use Illuminate\Foundation\Application;
use Carbon\Carbon;

class TemplateController
{

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

    // Add these two properties :Alankarika
    protected $useCustomTable = false;
    protected $courseId = null;
    protected $learningPathId = null;
    // end : Alankarika

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get all of the templates for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Collection
     */

    //Start : For Custom Notification :Alankarika
    /**
     * Enable course-specific template lookup.
     */
    public function forCourse(int $courseId): self
    {
        $this->useCustomTable = true;
        $this->courseId = $courseId;
        $this->learningPathId  = null;
        return $this;
    }

    public function forLearningPath(int $learningPathId): self
    {
        $this->useCustomTable = true;
        $this->learningPathId = $learningPathId;
        $this->courseId = null; //
        return $this;
    }

    /**
     * Reset course-specific lookup.
     */
    public function resetCourse(): self
    {
        $this->useCustomTable = false;
        $this->courseId = null;
        $this->learningPathId = null; // Add this line
        return $this;
    }

    // End : For Custom Notification : Alankarika
    public function forTemplate(Request $request)
    {
        $system_setting = getSystemSetting();
        $appLang = $this->app->config->get('app.locale') ? $this->app->config->get('app.locale') : $this->app->config->get('app.fallback_locale');
        return TemplateResource::collection(TemplateModel::search($request->filter)
            ->where('language', $appLang)
            ->when($system_setting->multi_portal == 0, function ($query) {
                $query->whereNotIn('event', array('set_password'));
            })
            ->when($request->sort_name != '', function ($query) use ($request) {
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
    public function forSelectedTemplate($templateId)
    {
        return new TemplateResource(TemplateModel::findOrFail($templateId));
    }

    /**
     * Get template using event .
     *
     * @param  $event
     * @param $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    // public function findTemplateUsingEvent($event, $data, $link = '')
    // {
    //     $appLang = $this->app->config->get('app.locale') ? $this->app->config->get('app.locale') : $this->app->config->get('app.fallback_locale');
    //     $templateData = TemplateModel::where('event', $event)
    //         ->where('language', $appLang)
    //         ->where('status', '1')
    //         ->first();

    //     if ($templateData) {
    //         $templateData = new TemplateResource($templateData);
    //         foreach ($data as $datakey => $value) {
    //             foreach (json_decode($templateData['placeholder']) as $key => $value) {
    //                 if ($key == $datakey) {
    //                     if (isset($data['connectionId'])) {
    //                         if ($key == 'COURSE_COMPLETION_DATE') {
    //                             if ($data['connectionId'] == 0) {
    //                                 $data[$datakey] = Carbon::parse($data[$datakey])->timezone(USER_TIMEZONE)->format(USER_DATE_FORMAT . ' H:i:s');
    //                             } else {
    //                                 $data[$datakey] = Carbon::now()->format('d-m-Y H:i:s');
    //                             }
    //                         }
    //                     } else {
    //                         if ($key == 'COURSE_COMPLETION_DATE') {
    //                             $data[$datakey] = Carbon::parse($data[$datakey])->timezone(USER_TIMEZONE)->format(USER_DATE_FORMAT . ' H:i:s');
    //                         }
    //                     }
    //                     if ($key == 'COURSE_ASSIGNMENT_DATE') {
    //                         $data[$datakey] = Carbon::parse($data[$datakey])->timezone(USER_TIMEZONE)->format(USER_DATE_FORMAT . ' H:i:s');
    //                     }
    //                     if ($key == 'COURSE_END_DATE') {
    //                         $data[$datakey] = Carbon::parse($data[$datakey])->timezone(USER_TIMEZONE)->format(USER_DATE_FORMAT . ' H:i:s');
    //                     }

    //                     $templateData['description'] = str_replace("[" . $key . "]", $data[$datakey], $templateData['description']);
    //                     break;
    //                 }
    //             }
    //         }

    //         if ($link != '') {
    //             $templateData['description'] = str_replace("[PASSWORD_RESET_URL]", $link, $templateData['description']);
    //         }
    //         return $templateData;
    //     }
    //     return [];
    // }

    // Start : Alankarika

    // dynamic table model
    public function findTemplateUsingEvent($event, $data, $link = '')
    {
        $appLang = $this->app->config->get('app.locale')
            ?: $this->app->config->get('app.fallback_locale');

        $templateData = null;

        // Step 1: If courseId is set, check course mapping table first
        if ($this->useCustomTable && ($this->courseId || $this->learningPathId)) {
            $courseModel = new TemplateModel();
            $courseModel->setTable('custom_email_templates');

            $templateData = $courseModel->newQuery()
                ->when($this->courseId, fn($q) => $q->where('course_id', $this->courseId))
                ->when($this->learningPathId, fn($q) => $q->where('learning_path_id', $this->learningPathId))
                ->where('event', $event)
                ->where('language', $appLang)
                ->where('status', '1')
                ->first();

            $this->resetCourse();
        }

        // Step 2: Fallback to email_templates if no course mapping found
        if (!$templateData) {
            $defaultModel = new TemplateModel();
            $defaultModel->setTable('email_templates'); // explicitly set default table

            $templateData = $defaultModel->newQuery()  // fresh query on default table
                ->where('event', $event)
                ->where('language', $appLang)
                ->where('status', '1')
                ->first();
        }

        // Step 3: Process and return
        if ($templateData) {
            $templateData = new TemplateResource($templateData);
            $templateData = $this->replacePlaceholders($templateData, $data, $link);
            return $templateData;
        }

        return [];
    }

    /**
     * Extract placeholder replacement into reusable method.
     */
    private function replacePlaceholders($templateData, $data, $link)
    {
        foreach ($data as $datakey => $value) {
            foreach (json_decode($templateData['placeholder']) as $key => $value) {
                if ($key == $datakey) {
                    if (isset($data['connectionId'])) {
                        if ($key == 'COURSE_COMPLETION_DATE') {
                            if ($data['connectionId'] == 0) {
                                $data[$datakey] = Carbon::parse($data[$datakey])->timezone(USER_TIMEZONE)->format(USER_DATE_FORMAT . ' H:i:s');
                            } else {
                                $data[$datakey] = Carbon::now()->format('d-m-Y H:i:s');
                            }
                        }
                    } else {
                        if ($key == 'COURSE_COMPLETION_DATE') {
                            $data[$datakey] = Carbon::parse($data[$datakey])->timezone(USER_TIMEZONE)->format(USER_DATE_FORMAT . ' H:i:s');
                        }
                    }
                    if ($key == 'COURSE_ASSIGNMENT_DATE') {
                        $data[$datakey] = Carbon::parse($data[$datakey])->timezone(USER_TIMEZONE)->format(USER_DATE_FORMAT . ' H:i:s');
                    }
                    if ($key == 'COURSE_END_DATE') {
                        $data[$datakey] = Carbon::parse($data[$datakey])->timezone(USER_TIMEZONE)->format(USER_DATE_FORMAT . ' H:i:s');
                    }

                    $templateData['description'] = str_replace("[" . $key . "]", $data[$datakey], $templateData['description']);
                    break;
                }
            }
        }

        if ($link != '') {
            $templateData['description'] = str_replace("[PASSWORD_RESET_URL]", $link, $templateData['description']);
        }

        return $templateData;
    }

    // End: Alankarika

    /**
     * Get template using language .
     *
     * @param  $event
     * @return \Illuminate\Database\Eloquent\Collection
     */
    // public function findTemplateUsingLanguage($language, $event)
    // {
    //     return new TemplateResource(TemplateModel::where('language', $language)
    //         ->where('event', $event)
    //         ->first());
    // }
    // Alankarika : change findTemplateUsingLanguage()  for custom notification

    public function getTemplate($language, $event, $courseId = null)
    {
        // Now you pass all three variables
        return $this->templateService->findTemplateUsingLanguage($language, $event, $courseId);
    }
    /**
     * Find a template by language and event, with an optional courseId.
     * * @param string $language
     * @param string $event
     * @param int|null $courseId  <-- Add this parameter
     * @return TemplateResource|null
     */
    public function findTemplateUsingLanguage($language, $event, $id = null, $type = null)
    {
        // Set the context based on the 'type' parameter
        if ($id && $type) {
            if ($type === 'learning_path') {
                $this->forLearningPath((int)$id);
            } else {
                // Default to course if type is 'course' or anything else
                $this->forCourse((int)$id);
            }
        }
        // Backward compatibility: if ID is passed without type, assume it's a course
        elseif ($id) {
            $this->forCourse((int)$id);
        }
        $templateData = null;

        // 1. Check Course Mapping Table
        if ($this->useCustomTable && ($this->courseId || $this->learningPathId)) {
            $courseModel = new TemplateModel();
            $courseModel->setTable('custom_email_templates');

            $templateData = $courseModel->newQuery()
                // ->where('course_id', $this->courseId)
                ->when($this->courseId, fn($q) => $q->where('course_id', $this->courseId))
                ->when($this->learningPathId, fn($q) => $q->where('learning_path_id', $this->learningPathId))
                ->where('event', $event)
                ->where('language', $language)
                ->where('status', '1')
                ->first();

            // Important: Reset state so the next request is clean
            $this->resetCourse();
        }

        // 2. Fallback to Default Table
        if (!$templateData) {
            $defaultModel = new TemplateModel();
            $defaultModel->setTable('email_templates');

            $templateData = $defaultModel->newQuery()
                ->where('event', $event)
                ->where('language', $language)
                ->where('status', '1')
                ->first();
        }

        return $templateData ? new TemplateResource($templateData) : null;
    }
    // end 

    /**
     * Store a new template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \rupalipshinde\template\Template
     */
    public function store(StoreTemplateRequest $request)
    {
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
            ),
            201
        );
    }

    /**
     * Update the given template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $templateId
     * @return \Illuminate\Http\Response|\rupalipshinde\template\Template
     */
    public function update(UpdateTemplateRequest $request, $event)
    {
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
        if (isset($request->cc_mail)) {
            if ($request->cc_mail) {
                $template->cc_mail = $request->cc_mail;
            }
        }

        $template->is_updated = '1';
        $template->save();
        return response(
            array(
                "message" => __('translations.updated_msg', array('attribute' => trans('translations.template'))),
                "status" => true,
            ),
            200
        );
    }

    /**
     * Update the status.
     *
     * @param  int $string
     * @param  string  $templateId
     * @return \Illuminate\Http\Response|\rupalipshinde\template\Template
     */
    public function updateTemplateStatus(Request $request, $status)
    {
        if (!in_array($status, array('0', '1'))) {
            return response(
                array(
                    "message" => __('validation.in', array('attribute' => trans('translations.status'))),
                    "status" => false,
                ),
                422
            );
        }
        $template = TemplateModel::where('event', $request->event)->update([
            'status' => $status,
        ]);
        return response(
            array(
                "message" => __('translations.updated_msg', array('attribute' => trans('translations.status'))),
                "status" => true,
            ),
            200
        );
    }

    /**
     * Delete the given template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $templateId
     * @return \Illuminate\Http\Response
     */
    public function destroy($templateId)
    {
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