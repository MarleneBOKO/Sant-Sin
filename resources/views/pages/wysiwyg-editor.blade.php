@extends('../layout/' . $layout)

@section('subhead')
    <title>Wysiwyg Editor - Midone - Tailwind HTML Admin Template</title>
@endsection

@section('subcontent')
    <div class="flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">CKEditor</h2>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <!-- BEGIN: Simple Editor -->
        <div class="col-span-12 lg:col-span-6">
            <div class="box">
                <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
                    <h2 class="font-medium text-base mr-auto">Simple Editor</h2>
                    <div class="w-full sm:w-auto flex items-center sm:ml-auto mt-3 sm:mt-0">
                        <div class="mr-3">Show example code</div>
                        <input data-target="#simple-editor" class="show-code input input--switch border" type="checkbox">
                    </div>
                </div>
                <div class="p-5" id="simple-editor">
                    <div class="preview">
                        <div data-simple-toolbar="true" class="editor" name="editor">
                            <p>Content of the editor.</p>
                        </div>
                    </div>
                    <div class="source-code hidden">
                        <button data-target="#copy-simple-editor" class="copy-code button button--sm border flex items-center text-gray-700 dark:border-dark-5 dark:text-gray-300">
                            <i data-feather="file" class="w-4 h-4 mr-2"></i> Copy example code
                        </button>
                        <div class="overflow-y-auto h-64 mt-3">
                            <pre class="source-preview" id="copy-simple-editor">
                                <code class="text-xs p-0 rounded-md html pl-5 pt-8 pb-4 -mb-10 -mt-10">
                                    {{ \Hp::formatCode('
                                        <div data-simple-toolbar="true" class="editor" name="editor">
                                            <p>Content of the editor.</p>
                                        </div>
                                    ') }}
                                </code>
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Simple Editor -->
        <!-- BEGIN: Standard Editor -->
        <div class="col-span-12 lg:col-span-6">
            <div class="box">
                <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
                    <h2 class="font-medium text-base mr-auto">Standard Editor</h2>
                    <div class="w-full sm:w-auto flex items-center sm:ml-auto mt-3 sm:mt-0">
                        <div class="mr-3">Show example code</div>
                        <input data-target="#standard-editor" class="show-code input input--switch border" type="checkbox">
                    </div>
                </div>
                <div class="p-5" id="standard-editor">
                    <div class="preview">
                        <div class="editor" name="editor">
                            <p>Content of the editor.</p>
                        </div>
                    </div>
                    <div class="source-code hidden">
                        <button data-target="#copy-standard-editor" class="copy-code button button--sm border flex items-center text-gray-700 dark:border-dark-5 dark:text-gray-300">
                            <i data-feather="file" class="w-4 h-4 mr-2"></i> Copy example code
                        </button>
                        <div class="overflow-y-auto h-64 mt-3">
                            <pre class="source-preview" id="copy-standard-editor">
                                <code class="text-xs p-0 rounded-md html pl-5 pt-8 pb-4 -mb-10 -mt-10">
                                    {{ \Hp::formatCode('
                                        <div class="editor" name="editor">
                                            <p>Content of the editor.</p>
                                        </div>
                                    ') }}
                                </code>
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Standard Editor -->
        <!-- BEGIN: Inline Editor -->
        <div class="col-span-12 lg:col-span-6">
            <div class="box">
                <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
                    <h2 class="font-medium text-base mr-auto">Inline Editor</h2>
                    <div class="w-full sm:w-auto flex items-center sm:ml-auto mt-3 sm:mt-0">
                        <div class="mr-3">Show example code</div>
                        <input data-target="#inline-editor" class="show-code input input--switch border" type="checkbox">
                    </div>
                </div>
                <div class="p-5" id="inline-editor">
                    <div class="preview">
                        <div data-editor="inline" class="editor" name="editor">
                            <p>Content of the editor.</p>
                        </div>
                    </div>
                    <div class="source-code hidden">
                        <button data-target="#copy-inline-editor" class="copy-code button button--sm border flex items-center text-gray-700 dark:border-dark-5 dark:text-gray-300">
                            <i data-feather="file" class="w-4 h-4 mr-2"></i> Copy example code
                        </button>
                        <div class="overflow-y-auto h-64 mt-3">
                            <pre class="source-preview" id="copy-inline-editor">
                                <code class="text-xs p-0 rounded-md html pl-5 pt-8 pb-4 -mb-10 -mt-10">
                                    {{ \Hp::formatCode('
                                        <div data-editor="inline" class="editor" name="editor">
                                            <p>Content of the editor.</p>
                                        </div>
                                    ') }}
                                </code>
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Inline Editor -->
        <!-- BEGIN: Balloon Editor -->
        <div class="col-span-12 lg:col-span-6">
            <div class="box">
                <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
                    <h2 class="font-medium text-base mr-auto">Balloon Editor</h2>
                    <div class="w-full sm:w-auto flex items-center sm:ml-auto mt-3 sm:mt-0">
                        <div class="mr-3">Show example code</div>
                        <input data-target="#balloon-editor" class="show-code input input--switch border" type="checkbox">
                    </div>
                </div>
                <div class="p-5" id="balloon-editor">
                    <div class="preview">
                        <div data-editor="balloon" class="editor" name="editor">
                            <p>Content of the editor.</p>
                        </div>
                    </div>
                    <div class="source-code hidden">
                        <button data-target="#copy-balloon-editor" class="copy-code button button--sm border flex items-center text-gray-700 dark:border-dark-5 dark:text-gray-300">
                            <i data-feather="file" class="w-4 h-4 mr-2"></i> Copy example code
                        </button>
                        <div class="overflow-y-auto h-64 mt-3">
                            <pre class="source-preview" id="copy-balloon-editor">
                                <code class="text-xs p-0 rounded-md html pl-5 pt-8 pb-4 -mb-10 -mt-10">
                                    {{ \Hp::formatCode('
                                        <div data-editor="balloon" class="editor" name="editor">
                                            <p>Content of the editor.</p>
                                        </div>
                                    ') }}
                                </code>
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Balloon Editor -->
        <!-- BEGIN: Document Editor -->
        <div class="col-span-12">
            <div class="box">
                <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
                    <h2 class="font-medium text-base mr-auto">Document Editor</h2>
                    <div class="w-full sm:w-auto flex items-center sm:ml-auto mt-3 sm:mt-0">
                        <div class="mr-3">Show example code</div>
                        <input data-target="#document-editor" class="show-code input input--switch border" type="checkbox">
                    </div>
                </div>
                <div class="p-5" id="document-editor">
                    <div class="preview">
                        <div data-editor="document" class="editor document-editor">
                            <div class="document-editor__toolbar"></div>
                            <div class="document-editor__editable-container">
                                <div class="document-editor__editable">
                                    <p>Content of the editor.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="source-code hidden">
                        <button data-target="#copy-document-editor" class="copy-code button button--sm border flex items-center text-gray-700 dark:border-dark-5 dark:text-gray-300">
                            <i data-feather="file" class="w-4 h-4 mr-2"></i> Copy example code
                        </button>
                        <div class="overflow-y-auto h-64 mt-3">
                            <pre class="source-preview" id="copy-document-editor">
                                <code class="text-xs p-0 rounded-md html pl-5 pt-8 pb-4 -mb-10 -mt-10">
                                    {{ \Hp::formatCode('
                                        <div data-editor="document" class="editor document-editor">
                                            <div class="document-editor__toolbar"></div>
                                            <div class="document-editor__editable-container">
                                                <div class="document-editor__editable">
                                                    <p>Content of the editor.</p>
                                                </div>
                                            </div>
                                        </div>
                                    ') }}
                                </code>
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Document Editor -->
    </div>    
@endsection