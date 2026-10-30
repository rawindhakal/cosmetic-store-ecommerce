export default {
    data() {
        return {
            isLeftColumnSectionDragging: false,
            isRightColumnSectionDragging: false,
            sections: {
                "product-form-left-sections": [],
                "product-form-right-sections": [],
            },
        };
    },

    computed: {
        storeSections() {
            return {
                get: (sortable) => {
                    return this.getSectionsOrder(sortable.el.dataset.name);
                },
                set: (sortable) => {
                    return this.setSectionsOrder(sortable);
                },
            };
        },
    },

    created() {
        this.sections["product-form-left-sections"] = this.getSectionsOrder(
            "product-form-left-sections"
        );
        this.sections["product-form-right-sections"] = this.getSectionsOrder(
            "product-form-right-sections"
        );
    },

    methods: {
        getDefaultSectionsOrder(key) {
            return {
                "product-form-left-sections": [
                    "attributes",
                    "variations",
                    "variants",
                    "options",
                    "downloads",
                ],
                "product-form-right-sections": [
                    "media",
                    "price",
                    "inventory",
                    "seo",
                    "additional",
                ],
            }[key];
        },

        getSectionsOrder(key) {
            const sectionsOrder = JSON.parse(localStorage.getItem(key));

            return sectionsOrder === null
                ? this.getDefaultSectionsOrder(key)
                : sectionsOrder;
        },

        setSectionsOrder(sortable) {
            this.$nextTick(() => {
                localStorage.setItem(
                    sortable.el.dataset.name,
                    JSON.stringify(this.sections[sortable.el.dataset.name])
                );
            });
        },

        enableContentSelection() {
            document.body.classList.remove("disable-content-selection");
        },

        disableContentSelection() {
            document.body.classList.add("disable-content-selection");
        },

        notifySectionOrderChange() {
            toaster(trans("product::products.section.order_saved"), {
                type: "default",
            });

            this.$nextTick(() => {
                this.initAllAttributeValuesSelectize();
                this.initColorPicker();
            });
        },
    },
};
