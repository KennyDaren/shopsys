import grapesjs from 'grapesjs';
import Translator from 'bazinga-translator';

export default grapesjs.plugins.add('text-with-image', editor => {
    editor.Blocks.add('textWithImage', {
        id: 'text-with-image',
        label: Translator.trans('Text with image'),
        category: 'Basic',
        media: '<svg xmlns="http://www.w3.org/2000/svg" width="48px" height="48px" viewBox="0 0 576 512"><path d="M528 32h-480C21.49 32 0 53.49 0 80V96h576V80C576 53.49 554.5 32 528 32zM0 432C0 458.5 21.49 480 48 480h480c26.51 0 48-21.49 48-48V128H0V432zM368 192h128C504.8 192 512 199.2 512 208S504.8 224 496 224h-128C359.2 224 352 216.8 352 208S359.2 192 368 192zM368 256h128C504.8 256 512 263.2 512 272S504.8 288 496 288h-128C359.2 288 352 280.8 352 272S359.2 256 368 256zM368 320h128c8.836 0 16 7.164 16 16S504.8 352 496 352h-128c-8.836 0-16-7.164-16-16S359.2 320 368 320zM176 192c35.35 0 64 28.66 64 64s-28.65 64-64 64s-64-28.66-64-64S140.7 192 176 192zM112 352h128c26.51 0 48 21.49 48 48c0 8.836-7.164 16-16 16h-192C71.16 416 64 408.8 64 400C64 373.5 85.49 352 112 352z"/></svg>',
        content: {
            type: 'text-with-image'
        }
    });

    editor.DomComponents.addType('text-with-image', {
        isComponent: element => element.classList && element.classList.contains('gjs-text-with-image'),
        model: {
            defaults: {
                attributes: {
                    class: ['gjs-text-with-image']
                },
                droppable: false,
                components: `
                    <div class="gjs-text-with-image-inner inner left">
                        <img class="image">
                        <div class="gjs-text-ckeditor text">Insert your text here</div>
                        <div class="clear" />    
                    </div>
                `
            }
        }
    });

    const imagePositionDataAttribute = 'data-image-position';
    const imageTypeDataAttribute = 'data-image-type';

    editor.DomComponents.addType('text-with-image-inner', {
        isComponent: element => element.classList && element.classList.contains('gjs-text-with-image-inner'),
        model: {
            init () {
                this.on(`change:attributes:${imagePositionDataAttribute}`, this.handleTypeChange);
                this.on(`change:attributes:${imageTypeDataAttribute}`, this.handleTypeChange);
            },

            handleTypeChange (element) {
                element.setClass(['gjs-text-with-image-inner', 'inner', `text-with-image-float-${this.getAttributes()[imagePositionDataAttribute]}`,
                    `text-with-image-type-${this.getAttributes()[imageTypeDataAttribute]}`
                ]);
            },
            defaults: {
                removable: false,
                draggable: false,
                copyable: false,
                droppable: false,
                propagate: ['removable', 'draggable', 'copyable', 'droppable'],
                attributes: {
                    [imagePositionDataAttribute]: 'left',
                    [imageTypeDataAttribute]: 'outside-layout',
                    class: ['gjs-text-with-image-inner', 'inner', 'left']
                },
                traits: [
                    {
                        type: 'select',
                        name: imagePositionDataAttribute,
                        label: Translator.trans('Position of image'),
                        options: [
                            {
                                id: 'left',
                                label: 'Left'
                            },
                            {
                                id: 'right',
                                label: 'Right'
                            }
                        ]
                    },
                    {
                        type: 'select',
                        name: imageTypeDataAttribute,
                        label: Translator.trans('Type of image'),
                        options: [
                            {
                                id: 'outside-layout',
                                label: Translator.trans('Outside layout')
                            },
                            {
                                id: 'inside-layout',
                                label: Translator.trans('Inside layout')
                            }
                        ]
                    },
                    {
                        type: 'input',
                        name: 'alt',
                        label: 'Alt'
                    }
                ]
            }
        }
    });
});
