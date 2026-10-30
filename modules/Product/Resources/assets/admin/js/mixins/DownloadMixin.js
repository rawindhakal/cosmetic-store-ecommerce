export default {
    watch: {
        "form.downloads": {
            deep: true,
            immediate: true,
            handler(newValue) {
                if (newValue.length === 0) {
                    this.addDownload();
                }
            },
        },
    },

    methods: {
        addDownload() {
            const downloads = this.form.downloads;

            downloads.push({
                id: null,
                filename: null,
            });
        },

        deleteDownload(index) {
            this.form.downloads.splice(index, 1);
        },

        chooseDownloadableFile(index) {
            let picker = new MediaPicker();

            picker.on("select", ({ id, filename }) => {
                this.form.downloads.splice(index, 1, {
                    id,
                    filename,
                });
            });
        },
    },
};
