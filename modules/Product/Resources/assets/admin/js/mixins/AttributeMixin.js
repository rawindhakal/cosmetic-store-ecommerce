export default {
    watch: {
        "form.attributes": {
            deep: true,
            immediate: true,
            handler(newValue) {
                if (newValue.length === 0) {
                    this.addAttribute();
                }
            },
        },
    },

    methods: {
        prepareAttributes(attributes) {
            attributes.forEach((attribute) => {
                attribute.uid = this.uid();
            });
        },

        getAttributeValuesById(id) {
            if (id === "") return;

            let values = null;

            for (const attributeSet of Object.values(this.attributeSets)) {
                for (const attribute of attributeSet.attributes) {
                    if (attribute.id === id) {
                        values = attribute.values;

                        return values;
                    }
                }
            }

            return values;
        },

        changeAttribute(attribute) {
            const attributeValuesSelectize = $(
                `#attributes-${attribute.uid}-values`
            )[0].selectize;

            attributeValuesSelectize.clear();
            attributeValuesSelectize.clearOptions();

            if (attribute.attribute_id) {
                this.getAttributeValuesById(attribute.attribute_id).forEach(
                    ({ id, value }) => {
                        attributeValuesSelectize.addOption({
                            value: id,
                            text: value,
                        });
                    }
                );

                attributeValuesSelectize.focus();
            }
        },

        initAttributeValuesSelectize(uid) {
            this.$nextTick(() => {
                $(`#attributes-${uid}-values`).selectize({
                    plugins: ["remove_button"],
                    onChange: (values) => {
                        const attribute = this.form.attributes.find(
                            (attribute) => attribute.uid === uid
                        );

                        attribute.values = values;

                        this.clearErrors({ name: "attributes", uid });
                    },
                });
            });
        },

        addAttribute() {
            const uid = this.uid();

            this.form.attributes.push({
                attribute_id: "",
                uid,
                values: [],
            });

            this.initAttributeValuesSelectize(uid);
        },

        deleteAttribute(index, uid) {
            $(`#attributes-${uid}-values`)[0].selectize.destroy();

            this.form.attributes.splice(index, 1);

            this.clearErrors({ name: "attributes", uid });
        },
    },
};
