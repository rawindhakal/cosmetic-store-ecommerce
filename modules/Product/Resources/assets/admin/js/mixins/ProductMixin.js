import md5 from "blueimp-md5";
import tinyMCE from "@admin/js/wysiwyg";
import flatPickr from "vue-flatpickr-component";
import SectionMixin from "./SectionMixin";
import AttributeMixin from "./AttributeMixin";
import VariationMixin from "./VariationMixin";
import BulkEditVariantsMixin from "./BulkEditVariantsMixin";
import VariantMixin from "./VariantMixin";
import OptionMixin from "./OptionMixin";
import DownloadMixin from "./DownloadMixin";
import DataTransformMixin from "./DataTransformMixin";
import { nprogress } from "@admin/js/NProgress";
import { toaster } from "@admin/js/Toaster";
import { generateUid, generateSlug } from "@admin/js/functions";

window.md5 = md5;
window.toaster = toaster;

export default {
    components: {
        flatPickr,
    },

    mixins: [
        SectionMixin,
        AttributeMixin,
        VariationMixin,
        BulkEditVariantsMixin,
        VariantMixin,
        OptionMixin,
        DownloadMixin,
        DataTransformMixin,
    ],

    data() {
        return {
            textEditor: null,
        };
    },

    mounted() {
        nprogress();

        this.fullscreenMode();
        this.initCategoriesSelectize();
        this.initTagsSelectize();
        this.initUpSellProductsSelectize();
        this.initCrossSellProductsSelectize();
        this.initRelatedProductsSelectize();

        this.textEditor = tinyMCE({
            setup: (editor) => {
                editor.on("change", () => {
                    editor.save();
                    editor.getElement().dispatchEvent(new Event("input"));

                    this.errors.clear("description");
                });
            },
        });
    },

    methods: {
        setProductSlug(value) {
            this.form.slug = generateSlug(value);
        },

        fullscreenMode() {
            $(".fullscreen-mode-open").on("click", (e) => {
                e.preventDefault();

                if (!document.fullscreenElement) {
                    $(".fullscreen-mode-open .fullscreen-one").removeClass(
                        "exit-full-screen"
                    );
                    $(".fullscreen-mode-open .fullscreen-two").addClass(
                        "enter-full-screen"
                    );

                    document.documentElement
                        .requestFullscreen()
                        .catch((err) => {
                            alert(
                                `Error attempting to enable full-screen mode: ${err.message} (${err.name})`
                            );
                        });
                } else {
                    $(".fullscreen-mode-open .fullscreen-two").removeClass(
                        "enter-full-screen"
                    );
                    $(".fullscreen-mode-open .fullscreen-one").addClass(
                        "exit-full-screen"
                    );

                    document.exitFullscreen().catch((err) => {
                        alert(
                            `Error attempting to disable full-screen mode: ${err.message} (${err.name})`
                        );
                    });
                }
            });
        },

        uid() {
            return generateUid();
        },

        focusEditor() {
            this.textEditor.get("description").focus();
        },

        focusField({ selector, key }) {
            if (key !== undefined) {
                this.errors.clear(key);
            }

            this.$nextTick(() => {
                $(`${selector}`).trigger("focus");
            });
        },

        initCategoriesSelectize() {
            $(this.$refs.categories).selectize({
                plugins: ["remove_button"],
                delimiter: ",",
                persist: true,
                selectOnTab: true,
                hideSelected: false,
                allowEmptyOption: true,
                onChange: (values) => {
                    this.form.categories = values;
                },
                onItemAdd(value) {
                    this.getItem(value)[0].innerHTML = this.getItem(
                        value
                    )[0].innerHTML.replace(/¦––\s/g, "");
                },
                onItemRemove(value) {
                    const element = [...this.$dropdown_content.children()].find(
                        (el) => el.getAttribute("data-value") === value
                    );

                    if (element) {
                        element.classList.remove("selected");
                    }
                },
                onInitialize() {
                    $("#categories")
                        .next()
                        .find("[data-value]")
                        .each((_, el) => {
                            $(el).html(
                                $(el)
                                    .text()
                                    .slice(0, -1)
                                    .replace(/¦––\s/g, "") +
                                    '<a href="javascript:void(0)" class="remove" tabindex="-1">×</a>'
                            );
                        });
                },
            });
        },

        initTagsSelectize() {
            $(this.$refs.tags).selectize({
                plugins: ["remove_button"],
                onChange: (values) => {
                    this.form.tags = values;
                },
            });
        },

        initAllAttributeValuesSelectize() {
            this.form.attributes.forEach((attribute, index) => {
                const options =
                    this.getAttributeValuesById(attribute.attribute_id)?.map(
                        (value) => {
                            return { value: value.id, text: value.value };
                        }
                    ) ?? [];

                $(`#attributes-${attribute.uid}-values`).selectize({
                    plugins: ["remove_button"],
                    onChange: (values) => {
                        this.form.attributes[index].values = values;

                        this.clearErrors({
                            name: "attributes",
                            uid: attribute.uid,
                        });
                    },
                    items: attribute.values,
                    options: [...options],
                });
            });
        },

        removeDatePickerValue(key) {
            this.form[key] = null;
        },

        removeVariantDatePickerValue(index, key) {
            this.form.variants[index][key] = null;
        },

        regenerateVariationsAndVariantsUid() {
            this.regenerateVariationsUid();

            const newVariants = this.generateNewVariants(
                this.getFilteredVariations()
            );

            newVariants.forEach(({ uids }, index) => {
                this.form.variants[index].uid = md5(uids);
                this.form.variants[index].uids = uids;
            });
        },

        hasAnyError({ name, uid }) {
            return Object.keys(this.errors.errors).some((key) =>
                key.startsWith(`${name}.${uid}`)
            );
        },

        clearErrors({ name, uid }) {
            this.clearMatchedErrors(`${name}.${uid}`);
        },

        clearValuesError({ name, uid }) {
            this.clearMatchedErrors(`${name}.${uid}.values`);
        },

        clearValueRowErrors({ name, uid, valueUid }) {
            this.clearMatchedErrors(`${name}.${uid}.values.${valueUid}`);
        },

        clearMatchedErrors(str) {
            Object.keys(this.errors.errors).forEach((key) => {
                if (key.startsWith(str)) {
                    this.errors.clear(key);
                }
            });
        },

        focusFirstErrorField(elements) {
            this.$nextTick(() => {
                [...elements]
                    .find(
                        (el) => el.name === Object.keys(this.errors.errors)[0]
                    )
                    .focus();
            });
        },

        addMedia() {
            const picker = new MediaPicker({ type: "image", multiple: true });

            picker.on("select", ({ id, path }) => {
                this.form.media.push({
                    id: +id,
                    path,
                });
            });
        },

        removeMedia(index) {
            this.form.media.splice(index, 1);
        },

        preventLastSlideDrag(event) {
            return event.related.className.indexOf("disabled") === -1;
        },

        toggleAccordions({ selector, state, data }) {
            const event = new Event("click");
            const elements = document.querySelectorAll(selector);

            if (!state) {
                data.forEach(({ is_open }, index) => {
                    if (is_open) {
                        elements[index].dispatchEvent(event);
                    }
                });

                return;
            }

            [...elements].forEach((element) => {
                element.dispatchEvent(event);
            });
        },

        toggleAccordion(event, data) {
            const target = $(event.currentTarget);
            const panelTitle = target.find('[data-toggle="collapse"]');
            const panelBody = target.next();

            if (data.is_open) {
                panelBody.css("display", "block");
            }

            panelTitle.attr("data-transition", true);

            data.is_open = !data.is_open;

            panelBody.slideToggle(300, () => {
                panelTitle.attr("data-transition", false);
                panelBody.removeAttr("style");
            });
        },

        setSearchableSelectizeConfig() {
            this.searchableSelectizeConfig = {
                plugins: ["remove_button"],
                valueField: "id",
                labelField: "name",
                searchField: "name",
                load: function (query, callback) {
                    const url = "/products";

                    if (url === undefined || query.length === 0) {
                        return callback();
                    }

                    axios
                        .get(url, {
                            params: {
                                query,
                            },
                        })
                        .then((response) => {
                            callback(response.data);
                        });
                },
            };
        },

        initUpSellProductsSelectize() {
            $(this.$refs.upSellProducts).selectize({
                ...this.searchableSelectizeConfig,
                onChange: (values) => {
                    this.form.up_sells = values;
                },
            });
        },

        initCrossSellProductsSelectize() {
            $(this.$refs.crossSellProducts).selectize({
                ...this.searchableSelectizeConfig,
                onChange: (values) => {
                    this.form.cross_sells = values;
                },
            });
        },

        initRelatedProductsSelectize() {
            $(this.$refs.relatedProducts).selectize({
                ...this.searchableSelectizeConfig,
                onChange: (values) => {
                    this.form.related_products = values;
                },
            });
        },
    },
};
