<template>
    <form
        class="product-form"
        @input="errors.clear($event.target.name)"
        @submit.prevent
        ref="form"
    >
        <div class="row">
            <div class="product-form-left-column col-lg-8 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h5>{{ trans("product::products.group.general") }}</h5>
                    </div>

                    <div class="box-body">
                        <div class="form-group row">
                            <label
                                for="name"
                                class="col-sm-12 control-label text-left"
                            >
                                {{ trans("product::attributes.name") }}
                                <span class="text-red">*</span>
                            </label>

                            <div class="col-sm-12">
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    class="form-control"
                                    v-model="form.name"
                                    @change="
                                        if (
                                            window.location.pathname.endsWith(
                                                'products/create'
                                            )
                                        ) {
                                            setProductSlug($event.target.value);
                                        }
                                    "
                                />

                                <span
                                    class="help-block text-red"
                                    v-if="errors.has('name')"
                                    v-text="errors.get('name')"
                                ></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label
                                for="description"
                                class="col-sm-12 control-label text-left"
                                @click="focusEditor"
                            >
                                {{ trans("product::attributes.description") }}
                                <span class="text-red">*</span>
                            </label>

                            <div class="col-sm-12">
                                <textarea
                                    name="description"
                                    id="description"
                                    class="form-control wysiwyg"
                                    v-model="form.description"
                                >
                                </textarea>

                                <span
                                    class="help-block text-red"
                                    v-if="errors.has('description')"
                                    v-text="errors.get('description')"
                                ></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label
                                for="brand-id"
                                class="col-sm-12 control-label text-left"
                            >
                                {{ trans("product::attributes.brand_id") }}
                            </label>

                            <div class="col-sm-6">
                                <select
                                    name="brand_id"
                                    id="brand-id"
                                    class="form-control custom-select-black"
                                    v-model="form.brand_id"
                                >
                                    <option value="">
                                        {{
                                            trans(
                                                "admin::admin.form.please_select"
                                            )
                                        }}
                                    </option>

                                    <option
                                        v-for="(brand, index, key) in brands"
                                        :key="key"
                                        :value="index"
                                    >
                                        {{ brand }}
                                    </option>
                                </select>

                                <span
                                    class="help-block text-red"
                                    v-if="errors.has('brand_id')"
                                    v-text="errors.get('brand_id')"
                                ></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label
                                for="categories"
                                class="col-sm-12 control-label text-left"
                            >
                                {{ trans("product::attributes.categories") }}
                            </label>

                            <div class="col-sm-6">
                                <select
                                    name="categories"
                                    id="categories"
                                    multiple
                                    v-model="form.categories"
                                    ref="categories"
                                >
                                    <option value="">
                                        {{
                                            trans(
                                                "admin::admin.form.please_select"
                                            )
                                        }}
                                    </option>

                                    <option
                                        v-for="(
                                            category, index, key
                                        ) in categories"
                                        :key="key"
                                        :value="index"
                                    >
                                        {{ category }}
                                    </option>
                                </select>

                                <span
                                    class="help-block text-red"
                                    v-if="errors.has('categories')"
                                    v-text="errors.get('categories')"
                                ></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label
                                for="tax-class-id"
                                class="col-sm-12 control-label text-left"
                            >
                                {{ trans("product::attributes.tax_class_id") }}
                            </label>

                            <div class="col-sm-6">
                                <select
                                    name="tax_class_id"
                                    id="tax-class-id"
                                    class="form-control custom-select-black"
                                    v-model="form.tax_class_id"
                                >
                                    <option value="">
                                        {{
                                            trans(
                                                "admin::admin.form.please_select"
                                            )
                                        }}
                                    </option>

                                    <option
                                        v-for="(
                                            taxClass, index, key
                                        ) in taxClasses"
                                        :key="key"
                                        :value="index"
                                    >
                                        {{ taxClass }}
                                    </option>
                                </select>

                                <span
                                    class="help-block text-red"
                                    v-if="errors.has('tax_class_id')"
                                    v-text="errors.get('tax_class_id')"
                                ></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label
                                for="tags"
                                class="col-sm-12 control-label text-left"
                            >
                                {{ trans("product::attributes.tags") }}
                            </label>

                            <div class="col-sm-6">
                                <select
                                    name="tags"
                                    id="tags"
                                    multiple
                                    v-model="form.tags"
                                    ref="tags"
                                >
                                    <option value="">
                                        {{
                                            trans(
                                                "admin::admin.form.please_select"
                                            )
                                        }}
                                    </option>

                                    <option
                                        v-for="(tag, index, key) in tags"
                                        :key="key"
                                        :value="index"
                                    >
                                        {{ tag }}
                                    </option>
                                </select>

                                <span
                                    class="help-block text-red"
                                    v-if="errors.has('tags')"
                                    v-text="errors.get('tags')"
                                ></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label
                                for="is_virtual"
                                class="col-sm-12 control-label text-left"
                            >
                                {{ trans("product::attributes.is_virtual") }}
                            </label>

                            <div class="col-sm-6">
                                <div class="switch">
                                    <input
                                        type="checkbox"
                                        name="is_virtual"
                                        id="is-virtual"
                                        v-model="form.is_virtual"
                                    />

                                    <label
                                        for="is-virtual"
                                        v-html="
                                            trans(
                                                'product::products.form.the_product_won\'t_be_shipped'
                                            )
                                        "
                                    >
                                    </label>
                                </div>

                                <span
                                    class="help-block text-red"
                                    v-if="errors.has('is_virtual')"
                                    v-text="errors.get('is_virtual')"
                                ></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label
                                for="is-active"
                                class="col-sm-12 control-label text-left"
                            >
                                {{ trans("product::attributes.is_active") }}
                            </label>

                            <div class="col-sm-9">
                                <div class="switch">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        id="is-active"
                                        v-model="form.is_active"
                                    />

                                    <label for="is-active">
                                        {{
                                            trans(
                                                "product::products.form.enable_the_product"
                                            )
                                        }}
                                    </label>

                                    <span
                                        class="help-block text-red"
                                        v-if="errors.has('is_active')"
                                        v-text="errors.get('is_active')"
                                    ></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <draggable
                    animation="150"
                    class="product-form-column"
                    :class="{ dragging: isLeftColumnSectionDragging }"
                    data-name="product-form-left-sections"
                    force-fallback="true"
                    item-key="element"
                    handle=".drag-handle"
                    :list="sections['product-form-left-sections']"
                    :store="storeSections"
                    @start="disableContentSelection"
                    @end="enableContentSelection"
                    @choose="isLeftColumnSectionDragging = true"
                    @unchoose="isLeftColumnSectionDragging = false"
                    @change="notifySectionOrderChange"
                >
                    <template #item="{ element: section }">
                        <div class="box">
                            <template v-if="section === 'attributes'">
                                <div class="box-header">
                                    <h5>
                                        {{
                                            trans(
                                                "product::products.group.attributes"
                                            )
                                        }}
                                    </h5>

                                    <div class="drag-handle">
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                    </div>
                                </div>

                                <div class="box-body">
                                    <div id="product-attributes-wrapper">
                                        <div class="table-responsive">
                                            <table
                                                class="options table table-bordered"
                                            >
                                                <thead class="hidden-xs">
                                                    <tr>
                                                        <th></th>
                                                        <th>
                                                            {{
                                                                trans(
                                                                    "product::products.attributes.attribute"
                                                                )
                                                            }}
                                                        </th>
                                                        <th>
                                                            {{
                                                                trans(
                                                                    "product::products.attributes.values"
                                                                )
                                                            }}
                                                        </th>
                                                        <th></th>
                                                    </tr>
                                                </thead>

                                                <tbody
                                                    is="vue:draggable"
                                                    animation="150"
                                                    handle=".drag-handle"
                                                    force-fallback="true"
                                                    item-key="uid"
                                                    tag="tbody"
                                                    @start="
                                                        disableContentSelection
                                                    "
                                                    @end="
                                                        enableContentSelection
                                                    "
                                                    :list="form.attributes"
                                                >
                                                    <template
                                                        #item="{
                                                            element: attribute,
                                                            index,
                                                        }"
                                                    >
                                                        <tr>
                                                            <td
                                                                class="text-center"
                                                            >
                                                                <span
                                                                    class="drag-handle"
                                                                >
                                                                    <i
                                                                        class="fa"
                                                                        >&#xf142;</i
                                                                    >
                                                                    <i
                                                                        class="fa"
                                                                        >&#xf142;</i
                                                                    >
                                                                </span>
                                                            </td>

                                                            <td>
                                                                <div
                                                                    class="form-group row"
                                                                >
                                                                    <label
                                                                        :for="`attributes-${attribute.uid}-attribute-id`"
                                                                        class="visible-xs"
                                                                    >
                                                                        {{
                                                                            trans(
                                                                                "product::products.attributes.attribute"
                                                                            )
                                                                        }}
                                                                    </label>

                                                                    <select
                                                                        :name="`attributes.${attribute.uid}.attribute_id`"
                                                                        :id="`attributes-${attribute.uid}-attribute-id`"
                                                                        class="form-control attribute custom-select-black"
                                                                        @change="
                                                                            changeAttribute(
                                                                                attribute
                                                                            )
                                                                        "
                                                                        v-model.number="
                                                                            attribute.attribute_id
                                                                        "
                                                                    >
                                                                        <option
                                                                            value=""
                                                                        >
                                                                            {{
                                                                                trans(
                                                                                    "admin::admin.form.please_select"
                                                                                )
                                                                            }}
                                                                        </option>

                                                                        <optgroup
                                                                            v-for="attributeSet in attributeSets"
                                                                            :key="
                                                                                attributeSet.id
                                                                            "
                                                                            :label="
                                                                                attributeSet.name
                                                                            "
                                                                        >
                                                                            <option
                                                                                v-for="attribute in attributeSet.attributes"
                                                                                :key="
                                                                                    attribute.id
                                                                                "
                                                                                :value="
                                                                                    attribute.id
                                                                                "
                                                                            >
                                                                                {{
                                                                                    attribute.name
                                                                                }}
                                                                            </option>
                                                                        </optgroup>
                                                                    </select>

                                                                    <span
                                                                        class="help-block text-red"
                                                                        v-if="
                                                                            errors.has(
                                                                                `attributes.${attribute.uid}.attribute_id`
                                                                            )
                                                                        "
                                                                        v-text="
                                                                            errors.get(
                                                                                `attributes.${attribute.uid}.attribute_id`
                                                                            )
                                                                        "
                                                                    >
                                                                    </span>
                                                                </div>
                                                            </td>

                                                            <td>
                                                                <div
                                                                    class="form-group row"
                                                                >
                                                                    <label
                                                                        :for="`attributes-${attribute.uid}-values`"
                                                                        class="visible-xs"
                                                                    >
                                                                        {{
                                                                            trans(
                                                                                "product::products.attributes.values"
                                                                            )
                                                                        }}
                                                                    </label>

                                                                    <select
                                                                        :name="`attributes.${attribute.uid}.values`"
                                                                        :id="`attributes-${attribute.uid}-values`"
                                                                        @input="
                                                                            clearValuesError(
                                                                                {
                                                                                    name: 'attributes',
                                                                                    uid: attribute.uid,
                                                                                }
                                                                            )
                                                                        "
                                                                        multiple
                                                                        v-model="
                                                                            attribute.values
                                                                        "
                                                                    ></select>

                                                                    <span
                                                                        class="help-block text-red"
                                                                        v-if="
                                                                            errors.has(
                                                                                `attributes.${attribute.uid}.values`
                                                                            )
                                                                        "
                                                                        v-text="
                                                                            errors.get(
                                                                                `attributes.${attribute.uid}.values`
                                                                            )
                                                                        "
                                                                    >
                                                                    </span>
                                                                </div>
                                                            </td>

                                                            <td
                                                                class="text-center"
                                                            >
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-default delete-row"
                                                                    @click="
                                                                        deleteAttribute(
                                                                            index,
                                                                            attribute.uid
                                                                        )
                                                                    "
                                                                >
                                                                    <i
                                                                        class="fa fa-trash"
                                                                    ></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>

                                        <button
                                            type="button"
                                            class="btn btn-default"
                                            @click="addAttribute"
                                        >
                                            {{
                                                trans(
                                                    "product::products.attributes.add_attribute"
                                                )
                                            }}
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template v-else-if="section === 'variations'">
                                <div class="box-header">
                                    <h5>
                                        {{
                                            trans(
                                                "product::products.group.variations"
                                            )
                                        }}
                                    </h5>

                                    <div class="d-flex">
                                        <span
                                            class="toggle-accordion"
                                            :class="{
                                                collapsed:
                                                    isCollapsedVariationsAccordion,
                                            }"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            :data-original-title="
                                                isCollapsedVariationsAccordion
                                                    ? trans(
                                                          'product::products.section.expand_all'
                                                      )
                                                    : trans(
                                                          'product::products.section.collapse_all'
                                                      )
                                            "
                                            @click="
                                                toggleAccordions({
                                                    selector:
                                                        '.variations-group .panel-heading',
                                                    state: isCollapsedVariationsAccordion,
                                                    data: form.variations,
                                                })
                                            "
                                        >
                                            <i
                                                class="fa fa-angle-double-up"
                                                aria-hidden="true"
                                            ></i>
                                        </span>

                                        <div class="drag-handle">
                                            <i
                                                class="fa fa-ellipsis-h"
                                                aria-hidden="true"
                                            ></i>
                                            <i
                                                class="fa fa-ellipsis-h"
                                                aria-hidden="true"
                                            ></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="box-body">
                                    <div class="accordion-box-content">
                                        <draggable
                                            animation="150"
                                            class="variations-group"
                                            force-fallback="true"
                                            item-key="uid"
                                            handle=".drag-handle"
                                            @start="disableContentSelection"
                                            @end="enableContentSelection"
                                            @change="reorderVariations"
                                            :list="form.variations"
                                        >
                                            <template
                                                #item="{
                                                    element: variation,
                                                    index,
                                                }"
                                            >
                                                <div
                                                    :id="`variation-${variation.uid}`"
                                                    class="content-accordion panel-group options-group-wrapper"
                                                    :class="`variation-${variation.uid}`"
                                                >
                                                    <div
                                                        class="panel panel-default"
                                                    >
                                                        <div
                                                            class="panel-heading"
                                                            @click.stop="
                                                                toggleAccordion(
                                                                    $event,
                                                                    variation
                                                                )
                                                            "
                                                        >
                                                            <h4
                                                                class="panel-title"
                                                            >
                                                                <div
                                                                    :aria-expanded="
                                                                        variation.is_open
                                                                    "
                                                                    data-toggle="collapse"
                                                                    data-transition="false"
                                                                    :class="{
                                                                        collapsed:
                                                                            !variation.is_open,
                                                                        'has-error':
                                                                            hasAnyError(
                                                                                {
                                                                                    name: 'variations',
                                                                                    uid: variation.uid,
                                                                                }
                                                                            ),
                                                                    }"
                                                                >
                                                                    <div
                                                                        class="d-flex align-items-center"
                                                                    >
                                                                        <span
                                                                            class="drag-handle"
                                                                        >
                                                                            <i
                                                                                class="fa"
                                                                                >&#xf142;</i
                                                                            >
                                                                            <i
                                                                                class="fa"
                                                                                >&#xf142;</i
                                                                            >
                                                                        </span>

                                                                        <span
                                                                            v-text="
                                                                                variation.name ||
                                                                                trans(
                                                                                    'product::products.variations.new_variation'
                                                                                )
                                                                            "
                                                                        ></span>
                                                                    </div>

                                                                    <span
                                                                        class="delete-option"
                                                                        @click.stop="
                                                                            deleteVariation(
                                                                                index,
                                                                                variation.uid
                                                                            )
                                                                        "
                                                                    >
                                                                        <i
                                                                            class="fa fa-trash"
                                                                        ></i>
                                                                    </span>
                                                                </div>
                                                            </h4>
                                                        </div>

                                                        <div
                                                            class="panel-collapse"
                                                            :class="{
                                                                collapse:
                                                                    !variation.is_open,
                                                            }"
                                                        >
                                                            <div
                                                                class="panel-body"
                                                            >
                                                                <div
                                                                    class="new-option"
                                                                >
                                                                    <div
                                                                        class="row"
                                                                    >
                                                                        <div
                                                                            class="col-sm-6"
                                                                        >
                                                                            <div
                                                                                class="form-group row"
                                                                            >
                                                                                <label
                                                                                    :for="`variations-${variation.uid}-name`"
                                                                                >
                                                                                    {{
                                                                                        trans(
                                                                                            "product::products.form.variations.name"
                                                                                        )
                                                                                    }}
                                                                                    <span
                                                                                        v-if="
                                                                                            variation.name ||
                                                                                            variation.type
                                                                                        "
                                                                                        class="text-red"
                                                                                        >*</span
                                                                                    >
                                                                                </label>

                                                                                <input
                                                                                    type="text"
                                                                                    :name="`variations.${variation.uid}.name`"
                                                                                    :id="`variations-${variation.uid}-name`"
                                                                                    class="form-control"
                                                                                    v-model="
                                                                                        variation.name
                                                                                    "
                                                                                />

                                                                                <span
                                                                                    class="help-block text-red"
                                                                                    v-if="
                                                                                        errors.has(
                                                                                            `variations.${variation.uid}.name`
                                                                                        )
                                                                                    "
                                                                                    v-text="
                                                                                        errors.get(
                                                                                            `variations.${variation.uid}.name`
                                                                                        )
                                                                                    "
                                                                                >
                                                                                </span>
                                                                            </div>
                                                                        </div>

                                                                        <div
                                                                            class="col-sm-3"
                                                                        >
                                                                            <div
                                                                                class="form-group row"
                                                                            >
                                                                                <label
                                                                                    :for="`variations-${variation.uid}-type`"
                                                                                >
                                                                                    {{
                                                                                        trans(
                                                                                            "product::products.form.variations.type"
                                                                                        )
                                                                                    }}
                                                                                    <span
                                                                                        v-if="
                                                                                            variation.name ||
                                                                                            variation.type
                                                                                        "
                                                                                        class="text-red"
                                                                                        >*</span
                                                                                    >
                                                                                </label>

                                                                                <select
                                                                                    :name="`variations.${variation.uid}.type`"
                                                                                    :id="`variations-${variation.uid}-type`"
                                                                                    class="form-control custom-select-black"
                                                                                    @change="
                                                                                        changeVariationType(
                                                                                            $event
                                                                                                .target
                                                                                                .value,
                                                                                            index,
                                                                                            variation.uid
                                                                                        )
                                                                                    "
                                                                                    v-model="
                                                                                        variation.type
                                                                                    "
                                                                                >
                                                                                    <option
                                                                                        value=""
                                                                                    >
                                                                                        {{
                                                                                            trans(
                                                                                                "product::products.form.variations.variation_types.please_select"
                                                                                            )
                                                                                        }}
                                                                                    </option>
                                                                                    <option
                                                                                        value="text"
                                                                                    >
                                                                                        {{
                                                                                            trans(
                                                                                                "product::products.form.variations.variation_types.text"
                                                                                            )
                                                                                        }}
                                                                                    </option>
                                                                                    <option
                                                                                        value="color"
                                                                                    >
                                                                                        {{
                                                                                            trans(
                                                                                                "product::products.form.variations.variation_types.color"
                                                                                            )
                                                                                        }}
                                                                                    </option>
                                                                                    <option
                                                                                        value="image"
                                                                                    >
                                                                                        {{
                                                                                            trans(
                                                                                                "product::products.form.variations.variation_types.image"
                                                                                            )
                                                                                        }}
                                                                                    </option>
                                                                                </select>

                                                                                <span
                                                                                    class="help-block text-red"
                                                                                    v-if="
                                                                                        errors.has(
                                                                                            `variations.${variation.uid}.type`
                                                                                        )
                                                                                    "
                                                                                    v-text="
                                                                                        errors.get(
                                                                                            `variations.${variation.uid}.type`
                                                                                        )
                                                                                    "
                                                                                >
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div
                                                                    class="variation-values"
                                                                    v-if="
                                                                        variation.type !==
                                                                        ''
                                                                    "
                                                                >
                                                                    <div
                                                                        class="table-responsive"
                                                                    >
                                                                        <table
                                                                            class="options table table-bordered table-striped"
                                                                            :class="
                                                                                variation.type !==
                                                                                ''
                                                                                    ? `type-${variation.type}`
                                                                                    : ''
                                                                            "
                                                                        >
                                                                            <thead>
                                                                                <tr>
                                                                                    <th></th>
                                                                                    <th>
                                                                                        {{
                                                                                            trans(
                                                                                                "product::products.form.variations.label"
                                                                                            )
                                                                                        }}
                                                                                        <span
                                                                                            class="text-red"
                                                                                            >*</span
                                                                                        >
                                                                                    </th>
                                                                                    <th
                                                                                        v-if="
                                                                                            variation.type ===
                                                                                            'color'
                                                                                        "
                                                                                    >
                                                                                        {{
                                                                                            trans(
                                                                                                "product::products.form.variations.color"
                                                                                            )
                                                                                        }}
                                                                                        <span
                                                                                            class="text-red"
                                                                                            >*</span
                                                                                        >
                                                                                    </th>
                                                                                    <th
                                                                                        v-else-if="
                                                                                            variation.type ===
                                                                                            'image'
                                                                                        "
                                                                                    >
                                                                                        {{
                                                                                            trans(
                                                                                                "product::products.form.variations.image"
                                                                                            )
                                                                                        }}
                                                                                        <span
                                                                                            class="text-red"
                                                                                            >*</span
                                                                                        >
                                                                                    </th>
                                                                                    <th></th>
                                                                                </tr>
                                                                            </thead>

                                                                            <tbody
                                                                                is="vue:draggable"
                                                                                animation="150"
                                                                                handle=".drag-handle"
                                                                                item-key="uid"
                                                                                tag="tbody"
                                                                                @change="
                                                                                    reorderVariationValues
                                                                                "
                                                                                :list="
                                                                                    variation.values
                                                                                "
                                                                            >
                                                                                <template
                                                                                    #item="{
                                                                                        element:
                                                                                            value,
                                                                                        index: valueIndex,
                                                                                    }"
                                                                                >
                                                                                    <tr
                                                                                        class="option-row"
                                                                                    >
                                                                                        <td
                                                                                            class="text-center"
                                                                                        >
                                                                                            <span
                                                                                                class="drag-handle"
                                                                                            >
                                                                                                <i
                                                                                                    class="fa"
                                                                                                    >&#xf142;</i
                                                                                                >
                                                                                                <i
                                                                                                    class="fa"
                                                                                                    >&#xf142;</i
                                                                                                >
                                                                                            </span>
                                                                                        </td>
                                                                                        <td>
                                                                                            <input
                                                                                                type="text"
                                                                                                :name="`variations.${variation.uid}.values.${value.uid}.label`"
                                                                                                :id="`variations-${variation.uid}-values-${value.uid}-label`"
                                                                                                class="form-control"
                                                                                                @input="
                                                                                                    generateVariants
                                                                                                "
                                                                                                @keyup.enter="
                                                                                                    addVariationRowOnPressEnter(
                                                                                                        $event,
                                                                                                        index,
                                                                                                        valueIndex
                                                                                                    )
                                                                                                "
                                                                                                v-model="
                                                                                                    value.label
                                                                                                "
                                                                                            />

                                                                                            <span
                                                                                                class="help-block text-red"
                                                                                                v-if="
                                                                                                    errors.has(
                                                                                                        `variations.${variation.uid}.values.${value.uid}.label`
                                                                                                    )
                                                                                                "
                                                                                                v-text="
                                                                                                    errors.get(
                                                                                                        `variations.${variation.uid}.values.${value.uid}.label`
                                                                                                    )
                                                                                                "
                                                                                            >
                                                                                            </span>
                                                                                        </td>
                                                                                        <td
                                                                                            v-if="
                                                                                                variation.type ===
                                                                                                'color'
                                                                                            "
                                                                                        >
                                                                                            <div>
                                                                                                <input
                                                                                                    type="text"
                                                                                                    :name="`variations.${variation.uid}.values.${value.uid}.color`"
                                                                                                    :id="`variations-${variation.uid}-values-${value.uid}-color`"
                                                                                                    class="form-control color-picker"
                                                                                                    v-model="
                                                                                                        value.color
                                                                                                    "
                                                                                                />
                                                                                            </div>

                                                                                            <span
                                                                                                class="help-block text-red"
                                                                                                v-if="
                                                                                                    errors.has(
                                                                                                        `variations.${variation.uid}.values.${value.uid}.color`
                                                                                                    )
                                                                                                "
                                                                                                v-text="
                                                                                                    errors.get(
                                                                                                        `variations.${variation.uid}.values.${value.uid}.color`
                                                                                                    )
                                                                                                "
                                                                                            >
                                                                                            </span>
                                                                                        </td>
                                                                                        <td
                                                                                            v-else-if="
                                                                                                variation.type ===
                                                                                                'image'
                                                                                            "
                                                                                        >
                                                                                            <div
                                                                                                class="d-flex"
                                                                                            >
                                                                                                <div
                                                                                                    class="image-holder"
                                                                                                    @click="
                                                                                                        chooseVariationImage(
                                                                                                            index,
                                                                                                            variation.uid,
                                                                                                            valueIndex,
                                                                                                            value.uid
                                                                                                        )
                                                                                                    "
                                                                                                >
                                                                                                    <template
                                                                                                        v-if="
                                                                                                            value
                                                                                                                .image
                                                                                                                .id
                                                                                                        "
                                                                                                    >
                                                                                                        <img
                                                                                                            :src="
                                                                                                                value
                                                                                                                    .image
                                                                                                                    .path
                                                                                                            "
                                                                                                            alt="variation image"
                                                                                                        />
                                                                                                    </template>

                                                                                                    <img
                                                                                                        v-else
                                                                                                        src="../../../../../../Admin/Resources/assets/images/placeholder_image.png"
                                                                                                        class="placeholder-image"
                                                                                                        alt="Placeholder Image"
                                                                                                    />
                                                                                                </div>
                                                                                            </div>

                                                                                            <span
                                                                                                class="help-block text-red"
                                                                                                v-if="
                                                                                                    errors.has(
                                                                                                        `variations.${variation.uid}.values.${value.uid}.image`
                                                                                                    )
                                                                                                "
                                                                                                v-text="
                                                                                                    errors.get(
                                                                                                        `variations.${variation.uid}.values.${value.uid}.image`
                                                                                                    )
                                                                                                "
                                                                                            >
                                                                                            </span>
                                                                                        </td>
                                                                                        <td
                                                                                            class="text-center"
                                                                                        >
                                                                                            <button
                                                                                                type="button"
                                                                                                tabindex="-1"
                                                                                                class="btn btn-default delete-row"
                                                                                                @click="
                                                                                                    deleteVariationRow(
                                                                                                        index,
                                                                                                        variation.uid,
                                                                                                        valueIndex,
                                                                                                        value.uid
                                                                                                    )
                                                                                                "
                                                                                            >
                                                                                                <i
                                                                                                    class="fa fa-trash"
                                                                                                ></i>
                                                                                            </button>
                                                                                        </td>
                                                                                    </tr>
                                                                                </template>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>

                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-default"
                                                                        @click="
                                                                            addVariationRow(
                                                                                index,
                                                                                variation.uid
                                                                            )
                                                                        "
                                                                    >
                                                                        {{
                                                                            trans(
                                                                                "product::products.variations.add_row"
                                                                            )
                                                                        }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </draggable>

                                        <div class="accordion-box-footer">
                                            <button
                                                type="button"
                                                class="btn btn-default"
                                                @click="addVariation"
                                            >
                                                {{
                                                    trans(
                                                        "product::products.variations.add_variation"
                                                    )
                                                }}
                                            </button>

                                            <div
                                                v-if="
                                                    hasAccess(
                                                        'admin.variations.index'
                                                    )
                                                "
                                                class="insert-template"
                                            >
                                                <select
                                                    class="form-control custom-select-black"
                                                    v-model="globalVariationId"
                                                >
                                                    <option value="">
                                                        {{
                                                            trans(
                                                                "product::products.form.variations.select_template"
                                                            )
                                                        }}
                                                    </option>

                                                    <option
                                                        v-for="globalVariation in globalVariations"
                                                        :key="
                                                            globalVariation.id
                                                        "
                                                        :value="
                                                            globalVariation.id
                                                        "
                                                    >
                                                        {{
                                                            globalVariation.name
                                                        }}
                                                    </option>
                                                </select>

                                                <button
                                                    type="button"
                                                    class="btn btn-default"
                                                    :class="{
                                                        'btn-loading':
                                                            addingGlobalVariation,
                                                    }"
                                                    :disabled="
                                                        isAddGlobalVariationDisabled
                                                    "
                                                    @click="addGlobalVariation"
                                                >
                                                    {{
                                                        trans(
                                                            "product::products.variations.insert"
                                                        )
                                                    }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template v-else-if="section === 'variants'">
                                <div class="box-header">
                                    <h5>
                                        {{
                                            trans(
                                                "product::products.group.variants"
                                            )
                                        }}
                                    </h5>

                                    <div class="d-flex">
                                        <span
                                            v-if="hasAnyVariant"
                                            class="toggle-accordion"
                                            :class="{
                                                collapsed:
                                                    isCollapsedVariantsAccordion,
                                            }"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            :data-original-title="
                                                isCollapsedVariantsAccordion
                                                    ? trans(
                                                          'product::products.section.expand_all'
                                                      )
                                                    : trans(
                                                          'product::products.section.collapse_all'
                                                      )
                                            "
                                            @click="
                                                toggleAccordions({
                                                    selector:
                                                        '.variants-group .panel-heading',
                                                    state: isCollapsedVariantsAccordion,
                                                    data: form.variants,
                                                })
                                            "
                                        >
                                            <i
                                                class="fa fa-angle-double-up"
                                                aria-hidden="true"
                                            ></i>
                                        </span>

                                        <div class="drag-handle">
                                            <i
                                                class="fa fa-ellipsis-h"
                                                aria-hidden="true"
                                            ></i>
                                            <i
                                                class="fa fa-ellipsis-h"
                                                aria-hidden="true"
                                            ></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="box-body">
                                    <div
                                        class="accordion-box-content form-horizontal"
                                    >
                                        <div
                                            v-if="!hasAnyVariant"
                                            class="alert alert-info"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                            >
                                                <path
                                                    d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM11.25 8C11.25 7.59 11.59 7.25 12 7.25C12.41 7.25 12.75 7.59 12.75 8V13C12.75 13.41 12.41 13.75 12 13.75C11.59 13.75 11.25 13.41 11.25 13V8ZM12.92 16.38C12.87 16.51 12.8 16.61 12.71 16.71C12.61 16.8 12.5 16.87 12.38 16.92C12.26 16.97 12.13 17 12 17C11.87 17 11.74 16.97 11.62 16.92C11.5 16.87 11.39 16.8 11.29 16.71C11.2 16.61 11.13 16.51 11.08 16.38C11.03 16.26 11 16.13 11 16C11 15.87 11.03 15.74 11.08 15.62C11.13 15.5 11.2 15.39 11.29 15.29C11.39 15.2 11.5 15.13 11.62 15.08C11.86 14.98 12.14 14.98 12.38 15.08C12.5 15.13 12.61 15.2 12.71 15.29C12.8 15.39 12.87 15.5 12.92 15.62C12.97 15.74 13 15.87 13 16C13 16.13 12.97 16.26 12.92 16.38Z"
                                                    fill="#555555"
                                                />
                                            </svg>

                                            <span>{{
                                                trans(
                                                    "product::products.variations.please_add_some_variations"
                                                )
                                            }}</span>
                                        </div>

                                        <template v-else>
                                            <div class="form-group row">
                                                <label
                                                    for="default-variant"
                                                    class="col-sm-3 control-label text-left"
                                                >
                                                    {{
                                                        trans(
                                                            "product::products.form.variants.default_variant"
                                                        )
                                                    }}
                                                </label>

                                                <div class="col-sm-5">
                                                    <select
                                                        name="default_variant"
                                                        id="default-variant"
                                                        class="form-control custom-select-black"
                                                        @change="
                                                            changeDefaultVariant(
                                                                $event.target
                                                                    .value
                                                            )
                                                        "
                                                    >
                                                        <option
                                                            v-for="(
                                                                variant, index
                                                            ) in form.variants"
                                                            :value="variant.uid"
                                                            :selected="
                                                                defaultVariantUid ===
                                                                variant.uid
                                                            "
                                                            :disabled="
                                                                !isActiveVariant(
                                                                    index
                                                                )
                                                            "
                                                        >
                                                            {{ variant.name }}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div
                                                class="bulk-edit-variants overflow-hidden"
                                            >
                                                <div class="form-group row">
                                                    <label
                                                        for="variation-values-list"
                                                        class="col-sm-3 control-label text-left"
                                                    >
                                                        {{
                                                            trans(
                                                                "product::products.form.variants.bulk_edit"
                                                            )
                                                        }}
                                                    </label>

                                                    <div class="col-sm-5">
                                                        <select
                                                            name="variation_values_list"
                                                            id="variation-values-list"
                                                            class="form-control custom-select-black"
                                                            @change="
                                                                changeBulkEditVariantsUid(
                                                                    $event
                                                                        .target
                                                                        .value
                                                                )
                                                            "
                                                            v-model="
                                                                bulkEditVariantsUid
                                                            "
                                                        >
                                                            <option value="">
                                                                {{
                                                                    trans(
                                                                        "admin::admin.form.please_select"
                                                                    )
                                                                }}
                                                            </option>
                                                            <option value="all">
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.all_variants"
                                                                    )
                                                                }}
                                                            </option>

                                                            <template
                                                                v-for="(
                                                                    variation,
                                                                    index
                                                                ) in form.variations"
                                                            >
                                                                <template
                                                                    v-for="(
                                                                        value,
                                                                        valueIndex
                                                                    ) in variation.values"
                                                                >
                                                                    <option
                                                                        v-if="
                                                                            variation.type !==
                                                                                '' &&
                                                                            Boolean(
                                                                                value.label
                                                                            )
                                                                        "
                                                                        :key="
                                                                            value.uid
                                                                        "
                                                                        :value="
                                                                            value.uid
                                                                        "
                                                                    >
                                                                        {{
                                                                            value.label
                                                                        }}
                                                                    </option>
                                                                </template>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div
                                                    v-if="
                                                        hasBulkEditVariantsUid
                                                    "
                                                    class="form-group row"
                                                >
                                                    <label
                                                        for="bulk-edit-variants-field-type"
                                                        class="col-sm-3 control-label text-left"
                                                    >
                                                        {{
                                                            trans(
                                                                "product::products.form.variants.field_type"
                                                            )
                                                        }}
                                                    </label>

                                                    <div class="col-sm-5">
                                                        <select
                                                            name="bulk_edit_variants_field_type"
                                                            id="bulk-edit-variants-field-type"
                                                            class="form-control custom-select-black"
                                                            @change="
                                                                changeBulkEditVariantsField(
                                                                    $event
                                                                        .target
                                                                        .value
                                                                )
                                                            "
                                                            v-model="
                                                                bulkEditVariantsField
                                                            "
                                                        >
                                                            <option value="">
                                                                {{
                                                                    trans(
                                                                        "admin::admin.form.please_select"
                                                                    )
                                                                }}
                                                            </option>
                                                            <option
                                                                value="is_active"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.is_active"
                                                                    )
                                                                }}
                                                            </option>
                                                            <option
                                                                value="media"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.media"
                                                                    )
                                                                }}
                                                            </option>
                                                            <option value="sku">
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.sku"
                                                                    )
                                                                }}
                                                            </option>
                                                            <option
                                                                value="price"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.price"
                                                                    )
                                                                }}
                                                            </option>
                                                            <option
                                                                value="special_price"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.special_price"
                                                                    )
                                                                }}
                                                            </option>
                                                            <option
                                                                value="manage_stock"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.manage_stock"
                                                                    )
                                                                }}
                                                            </option>
                                                            <option
                                                                value="in_stock"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.in_stock"
                                                                    )
                                                                }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <template
                                                    v-if="
                                                        hasBulkEditVariantsUid &&
                                                        hasBulkEditVariantsField
                                                    "
                                                >
                                                    <div
                                                        v-if="
                                                            bulkEditVariantsField ===
                                                            'is_active'
                                                        "
                                                        class="form-group row"
                                                    >
                                                        <label
                                                            for="bulk-edit-variants-is-active"
                                                            class="col-sm-3 control-label text-left"
                                                        >
                                                            {{
                                                                trans(
                                                                    "product::products.form.variants.is_active"
                                                                )
                                                            }}
                                                        </label>

                                                        <div class="col-sm-5">
                                                            <div
                                                                class="checkbox"
                                                            >
                                                                <input
                                                                    type="checkbox"
                                                                    name="bulk_edit_variants_is_active"
                                                                    id="bulk-edit-variants-is-active"
                                                                    v-model="
                                                                        bulkEditVariants.is_active
                                                                    "
                                                                />

                                                                <label
                                                                    for="bulk-edit-variants-is-active"
                                                                >
                                                                    {{
                                                                        trans(
                                                                            "product::products.form.variants.enable_the_variants"
                                                                        )
                                                                    }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div
                                                        v-else-if="
                                                            bulkEditVariantsField ===
                                                            'media'
                                                        "
                                                        class="form-group row"
                                                    >
                                                        <label
                                                            class="col-sm-3 control-label text-left"
                                                        >
                                                            {{
                                                                trans(
                                                                    "product::products.form.variants.media"
                                                                )
                                                            }}
                                                        </label>

                                                        <div class="col-sm-5">
                                                            <draggable
                                                                animation="200"
                                                                class="product-media-grid"
                                                                handle=".handle"
                                                                item-key="index"
                                                                :move="
                                                                    preventLastSlideDrag
                                                                "
                                                                :list="
                                                                    bulkEditVariants.media
                                                                "
                                                            >
                                                                <template
                                                                    #item="{
                                                                        element:
                                                                            media,
                                                                        index,
                                                                    }"
                                                                >
                                                                    <div
                                                                        class="media-grid-item handle"
                                                                    >
                                                                        <div
                                                                            class="image-holder"
                                                                        >
                                                                            <img
                                                                                :src="
                                                                                    media.path
                                                                                "
                                                                                alt="product variants media"
                                                                            />

                                                                            <button
                                                                                type="button"
                                                                                class="btn remove-image"
                                                                                @click="
                                                                                    removeBulkEditVariantsMedia(
                                                                                        index
                                                                                    )
                                                                                "
                                                                            >
                                                                                <svg
                                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                                    width="24"
                                                                                    height="24"
                                                                                    viewBox="0 0 24 24"
                                                                                    fill="none"
                                                                                >
                                                                                    <path
                                                                                        d="M6.00098 17.9995L17.9999 6.00053"
                                                                                        stroke="#292D32"
                                                                                        stroke-width="1.5"
                                                                                        stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                    />
                                                                                    <path
                                                                                        d="M17.9999 17.9995L6.00098 6.00055"
                                                                                        stroke="#292D32"
                                                                                        stroke-width="1.5"
                                                                                        stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                    />
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </template>

                                                                <template
                                                                    #footer
                                                                >
                                                                    <div
                                                                        class="media-grid-item media-picker disabled"
                                                                        @click="
                                                                            addBulkEditVariantsMedia
                                                                        "
                                                                    >
                                                                        <div
                                                                            class="image-holder"
                                                                        >
                                                                            <img
                                                                                src="../../../../../../Admin/Resources/assets/images/placeholder_image.png"
                                                                                class="placeholder-image"
                                                                                alt="Placeholder Image"
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </draggable>
                                                        </div>
                                                    </div>

                                                    <div
                                                        v-else-if="
                                                            bulkEditVariantsField ===
                                                            'sku'
                                                        "
                                                        class="form-group row"
                                                    >
                                                        <label
                                                            for="bulk-edit-variants-sku"
                                                            class="col-sm-3 control-label text-left"
                                                        >
                                                            {{
                                                                trans(
                                                                    "product::products.form.variants.sku"
                                                                )
                                                            }}
                                                        </label>

                                                        <div class="col-sm-5">
                                                            <input
                                                                type="text"
                                                                name="bulk_edit_variants_sku"
                                                                id="bulk-edit-variants-sku"
                                                                class="form-control"
                                                                v-model="
                                                                    bulkEditVariants.sku
                                                                "
                                                            />
                                                        </div>
                                                    </div>

                                                    <div
                                                        v-else-if="
                                                            bulkEditVariantsField ===
                                                            'price'
                                                        "
                                                        class="form-group row"
                                                    >
                                                        <label
                                                            for="bulk-edit-variants-price"
                                                            class="col-sm-3 control-label text-left"
                                                        >
                                                            {{
                                                                trans(
                                                                    "product::products.form.variants.price"
                                                                )
                                                            }}
                                                        </label>

                                                        <div class="col-sm-5">
                                                            <div
                                                                class="input-group"
                                                            >
                                                                <span
                                                                    class="input-group-addon"
                                                                >
                                                                    {{
                                                                        defaultCurrencySymbol
                                                                    }}
                                                                </span>

                                                                <input
                                                                    type="number"
                                                                    name="bulk_edit_variants_price"
                                                                    min="0"
                                                                    step="0.1"
                                                                    id="bulk-edit-variants-price"
                                                                    class="form-control"
                                                                    @wheel="
                                                                        $event.target.blur()
                                                                    "
                                                                    v-model.number="
                                                                        bulkEditVariants.price
                                                                    "
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <template
                                                        v-else-if="
                                                            bulkEditVariantsField ===
                                                            'special_price'
                                                        "
                                                    >
                                                        <div
                                                            class="form-group row"
                                                        >
                                                            <label
                                                                for="bulk-edit-variants-special-price"
                                                                class="col-sm-3 control-label text-left"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.special_price"
                                                                    )
                                                                }}
                                                            </label>

                                                            <div
                                                                class="col-sm-5"
                                                            >
                                                                <div
                                                                    class="input-group"
                                                                >
                                                                    <span
                                                                        class="input-group-addon"
                                                                    >
                                                                        {{
                                                                            bulkEditVariants.special_price_type ===
                                                                            "fixed"
                                                                                ? defaultCurrencySymbol
                                                                                : "%"
                                                                        }}
                                                                    </span>

                                                                    <input
                                                                        type="number"
                                                                        name="bulk_edit_variants_special_price"
                                                                        min="0"
                                                                        step="0.1"
                                                                        id="bulk-edit-variants-special-price"
                                                                        class="form-control"
                                                                        @wheel="
                                                                            $event.target.blur()
                                                                        "
                                                                        v-model="
                                                                            bulkEditVariants.special_price
                                                                        "
                                                                    />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="form-group row"
                                                        >
                                                            <label
                                                                for="bulk-edit-variants-special-price-type"
                                                                class="col-sm-3 control-label text-left"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.special_price_type"
                                                                    )
                                                                }}
                                                            </label>

                                                            <div
                                                                class="col-sm-5"
                                                            >
                                                                <select
                                                                    name="bulk_edit_variants_special_price_type"
                                                                    id="bulk-edit-variants-special-price-type"
                                                                    class="form-control custom-select-black"
                                                                    v-model="
                                                                        bulkEditVariants.special_price_type
                                                                    "
                                                                >
                                                                    <option
                                                                        value="fixed"
                                                                    >
                                                                        {{
                                                                            trans(
                                                                                "product::products.form.variants.special_price_types.fixed"
                                                                            )
                                                                        }}
                                                                    </option>

                                                                    <option
                                                                        value="percent"
                                                                    >
                                                                        {{
                                                                            trans(
                                                                                "product::products.form.variants.special_price_types.percent"
                                                                            )
                                                                        }}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="form-group row"
                                                        >
                                                            <label
                                                                for="bulk-edit-variants-special-price-start"
                                                                class="col-sm-3 control-label text-left"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.special_price_start"
                                                                    )
                                                                }}
                                                            </label>

                                                            <div
                                                                class="col-sm-5"
                                                            >
                                                                <flat-pickr
                                                                    name="bulk_edit_variants_special_price_start"
                                                                    id="bulk-edit-variants-special-price-start"
                                                                    class="form-control"
                                                                    :config="
                                                                        flatPickrConfig
                                                                    "
                                                                    v-model="
                                                                        bulkEditVariants.special_price_start
                                                                    "
                                                                >
                                                                </flat-pickr>
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="form-group row"
                                                        >
                                                            <label
                                                                for="bulk-edit-variants-special-price-end"
                                                                class="col-sm-3 control-label text-left"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.special_price_end"
                                                                    )
                                                                }}
                                                            </label>

                                                            <div
                                                                class="col-sm-5"
                                                            >
                                                                <flat-pickr
                                                                    name="bulk_edit_variants_special_price_end"
                                                                    id="bulk-edit-variants-special-price-end"
                                                                    class="form-control"
                                                                    :config="
                                                                        flatPickrConfig
                                                                    "
                                                                    v-model="
                                                                        bulkEditVariants.special_price_end
                                                                    "
                                                                >
                                                                </flat-pickr>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    <template
                                                        v-else-if="
                                                            bulkEditVariantsField ===
                                                            'manage_stock'
                                                        "
                                                    >
                                                        <div
                                                            class="form-group row"
                                                        >
                                                            <label
                                                                for="bulk-edit-variants-manage-stock"
                                                                class="col-sm-3 control-label text-left"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.manage_stock"
                                                                    )
                                                                }}
                                                            </label>

                                                            <div
                                                                class="col-sm-5"
                                                            >
                                                                <select
                                                                    name="bulk_edit_variants_manage_stock`"
                                                                    id="bulk-edit-variants-manage-stock"
                                                                    class="form-control custom-select-black"
                                                                    @change="
                                                                        focusField(
                                                                            {
                                                                                selector:
                                                                                    '#bulk-edit-variants-qty',
                                                                            }
                                                                        )
                                                                    "
                                                                    v-model="
                                                                        bulkEditVariants.manage_stock
                                                                    "
                                                                >
                                                                    <option
                                                                        value="0"
                                                                    >
                                                                        {{
                                                                            trans(
                                                                                "product::products.form.variants.manage_stock_states.0"
                                                                            )
                                                                        }}
                                                                    </option>

                                                                    <option
                                                                        value="1"
                                                                    >
                                                                        {{
                                                                            trans(
                                                                                "product::products.form.variants.manage_stock_states.1"
                                                                            )
                                                                        }}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div
                                                            v-if="
                                                                bulkEditVariants.manage_stock ==
                                                                1
                                                            "
                                                            class="form-group row"
                                                        >
                                                            <label
                                                                for="bulk-edit-variants-qty"
                                                                class="col-sm-3 control-label text-left"
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.form.variants.qty"
                                                                    )
                                                                }}
                                                            </label>

                                                            <div
                                                                class="col-sm-5"
                                                            >
                                                                <input
                                                                    type="number"
                                                                    name="bulk_edit_variants_qty"
                                                                    min="0"
                                                                    step="1"
                                                                    id="bulk-edit-variants-qty"
                                                                    class="form-control"
                                                                    @wheel="
                                                                        $event.target.blur()
                                                                    "
                                                                    v-model.number="
                                                                        bulkEditVariants.qty
                                                                    "
                                                                />
                                                            </div>
                                                        </div>
                                                    </template>

                                                    <div
                                                        v-else-if="
                                                            bulkEditVariantsField ===
                                                            'in_stock'
                                                        "
                                                        class="form-group row"
                                                    >
                                                        <label
                                                            for="bulk-edit-variants-in-stock`"
                                                            class="col-sm-3 control-label text-left"
                                                        >
                                                            {{
                                                                trans(
                                                                    "product::products.form.variants.in_stock"
                                                                )
                                                            }}
                                                        </label>

                                                        <div class="col-sm-5">
                                                            <select
                                                                name="bulk_edit_variants_in_stock`"
                                                                id="bulk-edit-variants-in-stock`"
                                                                class="form-control custom-select-black"
                                                                v-model="
                                                                    bulkEditVariants.in_stock
                                                                "
                                                            >
                                                                <option
                                                                    value="0"
                                                                >
                                                                    {{
                                                                        trans(
                                                                            "product::products.form.variants.stock_availability_states.0"
                                                                        )
                                                                    }}
                                                                </option>

                                                                <option
                                                                    value="1"
                                                                >
                                                                    {{
                                                                        trans(
                                                                            "product::products.form.variants.stock_availability_states.1"
                                                                        )
                                                                    }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div
                                                            class="col-sm-5 col-sm-offset-3"
                                                        >
                                                            <button
                                                                type="button"
                                                                class="btn btn-default"
                                                                @click="
                                                                    bulkUpdateVariants
                                                                "
                                                            >
                                                                {{
                                                                    trans(
                                                                        "product::products.variants.apply"
                                                                    )
                                                                }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>

                                            <transition-group
                                                tag="div"
                                                name="variant"
                                                class="variants-group"
                                            >
                                                <div
                                                    v-for="(
                                                        variant, index
                                                    ) in form.variants"
                                                    :id="`variant-${variant.uid}`"
                                                    class="content-accordion panel-group options-group-wrapper"
                                                    :key="variant.position"
                                                >
                                                    <div
                                                        class="panel panel-default"
                                                    >
                                                        <div
                                                            class="panel-heading"
                                                            @click.stop="
                                                                toggleAccordion(
                                                                    $event,
                                                                    variant
                                                                )
                                                            "
                                                        >
                                                            <h4
                                                                class="panel-title"
                                                            >
                                                                <div
                                                                    :aria-expanded="
                                                                        variant.is_open
                                                                    "
                                                                    data-toggle="collapse"
                                                                    :href="`#variant-collapse-${variant.uid}`"
                                                                    :class="{
                                                                        collapsed:
                                                                            !variant.is_open,
                                                                        'has-error':
                                                                            hasAnyError(
                                                                                {
                                                                                    name: 'variants',
                                                                                    uid: variant.uid,
                                                                                }
                                                                            ),
                                                                    }"
                                                                >
                                                                    <div
                                                                        class="d-flex align-items-center"
                                                                    >
                                                                        <div
                                                                            v-if="
                                                                                variant.is_selected
                                                                            "
                                                                            class="checkbox"
                                                                        >
                                                                            <input
                                                                                type="checkbox"
                                                                                :name="`variants.${variant.uid}.is_selected`"
                                                                                :id="`variants-${variant.uid}-is-selected`"
                                                                                :checked="
                                                                                    variant.is_selected
                                                                                "
                                                                                disabled
                                                                            />

                                                                            <label
                                                                                :for="`variants-${variant.uid}-is-selected`"
                                                                            ></label>
                                                                        </div>

                                                                        <span
                                                                            class="variant-name"
                                                                            >{{
                                                                                variant.name
                                                                            }}</span
                                                                        >

                                                                        <ul
                                                                            class="variant-badge list-inline d-flex"
                                                                        >
                                                                            <li
                                                                                v-if="
                                                                                    variant.is_default
                                                                                "
                                                                            >
                                                                                <span
                                                                                    class="label label-primary"
                                                                                >
                                                                                    {{
                                                                                        trans(
                                                                                            "product::products.variants.default"
                                                                                        )
                                                                                    }}
                                                                                </span>
                                                                            </li>
                                                                            <li
                                                                                v-else-if="
                                                                                    !variant.is_active
                                                                                "
                                                                            >
                                                                                <span
                                                                                    class="label label-default"
                                                                                >
                                                                                    {{
                                                                                        trans(
                                                                                            "product::products.variants.inactive"
                                                                                        )
                                                                                    }}
                                                                                </span>
                                                                            </li>
                                                                            <li
                                                                                v-if="
                                                                                    variant.is_active &&
                                                                                    (variant.in_stock ==
                                                                                        0 ||
                                                                                        (variant.manage_stock &&
                                                                                            Number(
                                                                                                variant.qty
                                                                                            ) ==
                                                                                                0))
                                                                                "
                                                                            >
                                                                                <span
                                                                                    class="label label-warning"
                                                                                >
                                                                                    {{
                                                                                        trans(
                                                                                            "product::products.variants.out_of_stock"
                                                                                        )
                                                                                    }}
                                                                                </span>
                                                                            </li>
                                                                        </ul>
                                                                    </div>

                                                                    <div
                                                                        class="switch"
                                                                        @click.stop
                                                                    >
                                                                        <input
                                                                            type="checkbox"
                                                                            :name="`variants.${variant.uid}.is_active`"
                                                                            :id="`variants-${variant.uid}-is-active`"
                                                                            :disabled="
                                                                                defaultVariantUid ===
                                                                                variant.uid
                                                                            "
                                                                            v-model="
                                                                                variant.is_active
                                                                            "
                                                                        />

                                                                        <label
                                                                            :for="`variants-${variant.uid}-is-active`"
                                                                            @click="
                                                                                changeVariantStatus(
                                                                                    variant.uid
                                                                                )
                                                                            "
                                                                        ></label>
                                                                    </div>
                                                                </div>
                                                            </h4>
                                                        </div>

                                                        <div
                                                            class="panel-collapse"
                                                            :class="{
                                                                collapse:
                                                                    !variant.is_open,
                                                            }"
                                                        >
                                                            <div
                                                                class="panel-body"
                                                            >
                                                                <div
                                                                    class="row"
                                                                >
                                                                    <div
                                                                        class="col-sm-4"
                                                                    >
                                                                        <draggable
                                                                            animation="200"
                                                                            class="product-media-grid"
                                                                            item-key="uid"
                                                                            handle=".handle"
                                                                            :move="
                                                                                preventLastSlideDrag
                                                                            "
                                                                            :list="
                                                                                variant.media
                                                                            "
                                                                        >
                                                                            <template
                                                                                #item="{
                                                                                    element:
                                                                                        media,
                                                                                    index: mediaIndex,
                                                                                }"
                                                                            >
                                                                                <div
                                                                                    class="media-grid-item handle"
                                                                                >
                                                                                    <div
                                                                                        class="image-holder"
                                                                                    >
                                                                                        <img
                                                                                            :src="
                                                                                                media.path
                                                                                            "
                                                                                            alt="product variant media"
                                                                                        />

                                                                                        <button
                                                                                            type="button"
                                                                                            class="btn remove-image"
                                                                                            @click="
                                                                                                removeVariantMedia(
                                                                                                    index,
                                                                                                    mediaIndex
                                                                                                )
                                                                                            "
                                                                                        >
                                                                                            <svg
                                                                                                xmlns="http://www.w3.org/2000/svg"
                                                                                                width="24"
                                                                                                height="24"
                                                                                                viewBox="0 0 24 24"
                                                                                                fill="none"
                                                                                            >
                                                                                                <path
                                                                                                    d="M6.00098 17.9995L17.9999 6.00053"
                                                                                                    stroke="#292D32"
                                                                                                    stroke-width="1.5"
                                                                                                    stroke-linecap="round"
                                                                                                    stroke-linejoin="round"
                                                                                                />
                                                                                                <path
                                                                                                    d="M17.9999 17.9995L6.00098 6.00055"
                                                                                                    stroke="#292D32"
                                                                                                    stroke-width="1.5"
                                                                                                    stroke-linecap="round"
                                                                                                    stroke-linejoin="round"
                                                                                                />
                                                                                            </svg>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </template>

                                                                            <template
                                                                                #footer
                                                                            >
                                                                                <div
                                                                                    class="media-grid-item media-picker disabled"
                                                                                    @click="
                                                                                        addVariantMedia(
                                                                                            index
                                                                                        )
                                                                                    "
                                                                                >
                                                                                    <div
                                                                                        class="image-holder"
                                                                                    >
                                                                                        <img
                                                                                            src="../../../../../../Admin/Resources/assets/images/placeholder_image.png"
                                                                                            class="placeholder-image"
                                                                                            alt="Placeholder Image"
                                                                                        />
                                                                                    </div>
                                                                                </div>
                                                                            </template>
                                                                        </draggable>
                                                                    </div>

                                                                    <div
                                                                        class="col-sm-8"
                                                                    >
                                                                        <div
                                                                            class="variant-fields"
                                                                        >
                                                                            <div
                                                                                class="row"
                                                                            >
                                                                                <div
                                                                                    class="col-sm-6"
                                                                                >
                                                                                    <div
                                                                                        class="form-group row"
                                                                                    >
                                                                                        <label
                                                                                            :for="`variants-${variant.uid}-sku`"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.variants.sku"
                                                                                                )
                                                                                            }}
                                                                                        </label>

                                                                                        <input
                                                                                            type="text"
                                                                                            :name="`variants.${variant.uid}.sku`"
                                                                                            :id="`variants-${variant.uid}-sku`"
                                                                                            class="form-control"
                                                                                            v-model="
                                                                                                variant.sku
                                                                                            "
                                                                                        />

                                                                                        <span
                                                                                            class="help-block text-red"
                                                                                            v-if="
                                                                                                errors.has(
                                                                                                    `variants.${variant.uid}.sku`
                                                                                                )
                                                                                            "
                                                                                            v-text="
                                                                                                errors.get(
                                                                                                    `variants.${variant.uid}.sku`
                                                                                                )
                                                                                            "
                                                                                        >
                                                                                        </span>
                                                                                    </div>
                                                                                </div>

                                                                                <div
                                                                                    class="col-sm-6"
                                                                                >
                                                                                    <div
                                                                                        class="form-group row"
                                                                                    >
                                                                                        <label
                                                                                            :for="`variants-${variant.uid}-price`"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.variants.price"
                                                                                                )
                                                                                            }}
                                                                                            <span
                                                                                                class="text-red"
                                                                                                >*</span
                                                                                            >
                                                                                        </label>

                                                                                        <div
                                                                                            class="input-group"
                                                                                        >
                                                                                            <span
                                                                                                class="input-group-addon"
                                                                                            >
                                                                                                {{
                                                                                                    defaultCurrencySymbol
                                                                                                }}
                                                                                            </span>

                                                                                            <input
                                                                                                type="number"
                                                                                                :name="`variants.${variant.uid}.price`"
                                                                                                min="0"
                                                                                                step="0.1"
                                                                                                :id="`variants-${variant.uid}-price`"
                                                                                                class="form-control"
                                                                                                @wheel="
                                                                                                    $event.target.blur()
                                                                                                "
                                                                                                v-model.number="
                                                                                                    variant.price
                                                                                                "
                                                                                            />
                                                                                        </div>

                                                                                        <span
                                                                                            class="help-block text-red"
                                                                                            v-if="
                                                                                                errors.has(
                                                                                                    `variants.${variant.uid}.price`
                                                                                                )
                                                                                            "
                                                                                            v-text="
                                                                                                errors.get(
                                                                                                    `variants.${variant.uid}.price`
                                                                                                )
                                                                                            "
                                                                                        >
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div
                                                                                class="row"
                                                                            >
                                                                                <div
                                                                                    class="col-sm-6"
                                                                                >
                                                                                    <div
                                                                                        class="form-group row"
                                                                                    >
                                                                                        <label
                                                                                            :for="`variants-${variant.uid}-special-price`"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.variants.special_price"
                                                                                                )
                                                                                            }}
                                                                                        </label>

                                                                                        <div
                                                                                            class="input-group"
                                                                                        >
                                                                                            <span
                                                                                                class="input-group-addon"
                                                                                            >
                                                                                                {{
                                                                                                    variant.special_price_type ===
                                                                                                    "fixed"
                                                                                                        ? defaultCurrencySymbol
                                                                                                        : "%"
                                                                                                }}
                                                                                            </span>

                                                                                            <input
                                                                                                type="number"
                                                                                                :name="`variants.${variant.uid}.special_price`"
                                                                                                min="0"
                                                                                                step="0.1"
                                                                                                :id="`variants-${variant.uid}-special-price`"
                                                                                                class="form-control"
                                                                                                @wheel="
                                                                                                    $event.target.blur()
                                                                                                "
                                                                                                v-model="
                                                                                                    variant.special_price
                                                                                                "
                                                                                            />
                                                                                        </div>

                                                                                        <span
                                                                                            class="help-block text-red"
                                                                                            v-if="
                                                                                                errors.has(
                                                                                                    `variants.${variant.uid}.special_price`
                                                                                                )
                                                                                            "
                                                                                            v-text="
                                                                                                errors.get(
                                                                                                    `variants.${variant.uid}.special_price`
                                                                                                )
                                                                                            "
                                                                                        >
                                                                                        </span>
                                                                                    </div>
                                                                                </div>

                                                                                <div
                                                                                    class="col-sm-6"
                                                                                >
                                                                                    <div
                                                                                        class="form-group row"
                                                                                    >
                                                                                        <label
                                                                                            :for="`variants-${variant.uid}-special-price-type`"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.variants.special_price_type"
                                                                                                )
                                                                                            }}
                                                                                        </label>

                                                                                        <select
                                                                                            :name="`variants.${variant.uid}.special_price_type`"
                                                                                            :id="`variants-${variant.uid}-special-price-type`"
                                                                                            class="form-control custom-select-black"
                                                                                            v-model="
                                                                                                variant.special_price_type
                                                                                            "
                                                                                        >
                                                                                            <option
                                                                                                value="fixed"
                                                                                            >
                                                                                                {{
                                                                                                    trans(
                                                                                                        "product::products.form.variants.special_price_types.fixed"
                                                                                                    )
                                                                                                }}
                                                                                            </option>

                                                                                            <option
                                                                                                value="percent"
                                                                                            >
                                                                                                {{
                                                                                                    trans(
                                                                                                        "product::products.form.variants.special_price_types.percent"
                                                                                                    )
                                                                                                }}
                                                                                            </option>
                                                                                        </select>

                                                                                        <span
                                                                                            class="help-block text-red"
                                                                                            v-if="
                                                                                                errors.has(
                                                                                                    `variants.${variant.uid}.special_price_type`
                                                                                                )
                                                                                            "
                                                                                            v-text="
                                                                                                errors.get(
                                                                                                    `variants.${variant.uid}.special_price_type`
                                                                                                )
                                                                                            "
                                                                                        >
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div
                                                                                class="row"
                                                                            >
                                                                                <div
                                                                                    class="col-sm-6"
                                                                                >
                                                                                    <div
                                                                                        class="form-group row"
                                                                                    >
                                                                                        <label
                                                                                            :for="`variants-${variant.uid}-special-price-start`"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.variants.special_price_start"
                                                                                                )
                                                                                            }}
                                                                                        </label>

                                                                                        <div
                                                                                            class="input-group"
                                                                                        >
                                                                                            <span
                                                                                                class="input-group-addon"
                                                                                            >
                                                                                                <i
                                                                                                    class="fa fa-calendar"
                                                                                                    aria-hidden="true"
                                                                                                ></i>
                                                                                            </span>

                                                                                            <flat-pickr
                                                                                                :name="`variants.${variant.uid}.special_price_start`"
                                                                                                :id="`variants-${variant.uid}-special-price-start`"
                                                                                                class="form-control"
                                                                                                :config="
                                                                                                    flatPickrConfig
                                                                                                "
                                                                                                v-model="
                                                                                                    variant.special_price_start
                                                                                                "
                                                                                            >
                                                                                            </flat-pickr>

                                                                                            <span
                                                                                                class="input-group-addon cursor-pointer"
                                                                                                v-if="
                                                                                                    variant.special_price_start
                                                                                                "
                                                                                                @click="
                                                                                                    removeVariantDatePickerValue(
                                                                                                        index,
                                                                                                        'special_price_start'
                                                                                                    )
                                                                                                "
                                                                                            >
                                                                                                <i
                                                                                                    class="fa fa-times"
                                                                                                    aria-hidden="true"
                                                                                                ></i>
                                                                                            </span>
                                                                                        </div>

                                                                                        <span
                                                                                            class="help-block text-red"
                                                                                            v-if="
                                                                                                errors.has(
                                                                                                    `variants.${variant.uid}.special_price_start`
                                                                                                )
                                                                                            "
                                                                                            v-text="
                                                                                                errors.get(
                                                                                                    `variants.${variant.uid}.special_price_start`
                                                                                                )
                                                                                            "
                                                                                        >
                                                                                        </span>
                                                                                    </div>
                                                                                </div>

                                                                                <div
                                                                                    class="col-sm-6"
                                                                                >
                                                                                    <div
                                                                                        class="form-group row"
                                                                                    >
                                                                                        <label
                                                                                            :for="`variants-${variant.uid}-special-price-end`"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.variants.special_price_end"
                                                                                                )
                                                                                            }}
                                                                                        </label>

                                                                                        <div
                                                                                            class="input-group"
                                                                                        >
                                                                                            <span
                                                                                                class="input-group-addon"
                                                                                            >
                                                                                                <i
                                                                                                    class="fa fa-calendar"
                                                                                                    aria-hidden="true"
                                                                                                ></i>
                                                                                            </span>

                                                                                            <flat-pickr
                                                                                                :name="`variants.${variant.uid}.special_price_end`"
                                                                                                :id="`variants-${variant.uid}-special-price-end`"
                                                                                                class="form-control"
                                                                                                :config="
                                                                                                    flatPickrConfig
                                                                                                "
                                                                                                v-model="
                                                                                                    variant.special_price_end
                                                                                                "
                                                                                            >
                                                                                            </flat-pickr>

                                                                                            <span
                                                                                                class="input-group-addon cursor-pointer"
                                                                                                v-if="
                                                                                                    variant.special_price_end
                                                                                                "
                                                                                                @click="
                                                                                                    removeVariantDatePickerValue(
                                                                                                        index,
                                                                                                        'special_price_end'
                                                                                                    )
                                                                                                "
                                                                                            >
                                                                                                <i
                                                                                                    class="fa fa-times"
                                                                                                    aria-hidden="true"
                                                                                                ></i>
                                                                                            </span>
                                                                                        </div>

                                                                                        <span
                                                                                            class="help-block text-red"
                                                                                            v-if="
                                                                                                errors.has(
                                                                                                    `variants.${variant.uid}.special_price_end`
                                                                                                )
                                                                                            "
                                                                                            v-text="
                                                                                                errors.get(
                                                                                                    `variants.${variant.uid}.special_price_end`
                                                                                                )
                                                                                            "
                                                                                        >
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div
                                                                                class="row"
                                                                            >
                                                                                <div
                                                                                    class="col-sm-6"
                                                                                >
                                                                                    <div
                                                                                        class="form-group row"
                                                                                    >
                                                                                        <label
                                                                                            :for="`variants-${variant.uid}-manage-stock`"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.variants.manage_stock"
                                                                                                )
                                                                                            }}
                                                                                        </label>

                                                                                        <select
                                                                                            :name="`variants.${variant.uid}.manage_stock`"
                                                                                            :id="`variants-${variant.uid}-manage-stock`"
                                                                                            class="form-control custom-select-black"
                                                                                            @change="
                                                                                                focusField(
                                                                                                    {
                                                                                                        selector: `#variants-${variant.uid}-qty`,
                                                                                                        key: `variants.${variant.uid}.qty`,
                                                                                                    }
                                                                                                )
                                                                                            "
                                                                                            v-model.number="
                                                                                                variant.manage_stock
                                                                                            "
                                                                                        >
                                                                                            <option
                                                                                                value="0"
                                                                                            >
                                                                                                {{
                                                                                                    trans(
                                                                                                        "product::products.form.variants.manage_stock_states.0"
                                                                                                    )
                                                                                                }}
                                                                                            </option>

                                                                                            <option
                                                                                                value="1"
                                                                                            >
                                                                                                {{
                                                                                                    trans(
                                                                                                        "product::products.form.variants.manage_stock_states.1"
                                                                                                    )
                                                                                                }}
                                                                                            </option>
                                                                                        </select>

                                                                                        <span
                                                                                            class="help-block text-red"
                                                                                            v-if="
                                                                                                errors.has(
                                                                                                    `variants.${variant.uid}.manage_stock`
                                                                                                )
                                                                                            "
                                                                                            v-text="
                                                                                                errors.get(
                                                                                                    `variants.${variant.uid}.manage_stock`
                                                                                                )
                                                                                            "
                                                                                        >
                                                                                        </span>
                                                                                    </div>
                                                                                </div>

                                                                                <div
                                                                                    v-if="
                                                                                        variant.manage_stock ==
                                                                                        1
                                                                                    "
                                                                                    class="col-sm-6"
                                                                                >
                                                                                    <div
                                                                                        class="form-group row"
                                                                                    >
                                                                                        <label
                                                                                            :for="`variants-${variant.uid}-qty`"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.variants.qty"
                                                                                                )
                                                                                            }}<span
                                                                                                class="text-red"
                                                                                                >*</span
                                                                                            >
                                                                                        </label>

                                                                                        <input
                                                                                            type="number"
                                                                                            :name="`variants.${variant.uid}.qty`"
                                                                                            min="0"
                                                                                            step="1"
                                                                                            :id="`variants-${variant.uid}-qty`"
                                                                                            class="form-control"
                                                                                            @wheel="
                                                                                                $event.target.blur()
                                                                                            "
                                                                                            v-model.number="
                                                                                                variant.qty
                                                                                            "
                                                                                        />

                                                                                        <span
                                                                                            class="help-block text-red"
                                                                                            v-if="
                                                                                                errors.has(
                                                                                                    `variants.${variant.uid}.qty`
                                                                                                )
                                                                                            "
                                                                                            v-text="
                                                                                                errors.get(
                                                                                                    `variants.${variant.uid}.qty`
                                                                                                )
                                                                                            "
                                                                                        >
                                                                                        </span>
                                                                                    </div>
                                                                                </div>

                                                                                <div
                                                                                    class="col-sm-6"
                                                                                >
                                                                                    <div
                                                                                        class="form-group row"
                                                                                    >
                                                                                        <label
                                                                                            :for="`variants-${variant.uid}-in-stock`"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.variants.in_stock"
                                                                                                )
                                                                                            }}
                                                                                        </label>

                                                                                        <select
                                                                                            :name="`variants.${variant.uid}.in_stock`"
                                                                                            :id="`variants-${variant.uid}-in-stock`"
                                                                                            class="form-control custom-select-black"
                                                                                            v-model="
                                                                                                variant.in_stock
                                                                                            "
                                                                                        >
                                                                                            <option
                                                                                                value="0"
                                                                                            >
                                                                                                {{
                                                                                                    trans(
                                                                                                        "product::products.form.variants.stock_availability_states.0"
                                                                                                    )
                                                                                                }}
                                                                                            </option>

                                                                                            <option
                                                                                                value="1"
                                                                                            >
                                                                                                {{
                                                                                                    trans(
                                                                                                        "product::products.form.variants.stock_availability_states.1"
                                                                                                    )
                                                                                                }}
                                                                                            </option>
                                                                                        </select>

                                                                                        <span
                                                                                            class="help-block text-red"
                                                                                            v-if="
                                                                                                errors.has(
                                                                                                    `variants.${variant.uid}.in_stock`
                                                                                                )
                                                                                            "
                                                                                            v-text="
                                                                                                errors.get(
                                                                                                    `variants.${variant.uid}.in_stock`
                                                                                                )
                                                                                            "
                                                                                        >
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </transition-group>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template v-else-if="section === 'options'">
                                <div class="box-header">
                                    <h5>
                                        {{
                                            trans(
                                                "product::products.group.options"
                                            )
                                        }}
                                    </h5>

                                    <div class="d-flex">
                                        <span
                                            class="toggle-accordion"
                                            :class="{
                                                collapsed:
                                                    isCollapsedOptionsAccordion,
                                            }"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            :data-original-title="
                                                isCollapsedOptionsAccordion
                                                    ? trans(
                                                          'product::products.section.expand_all'
                                                      )
                                                    : trans(
                                                          'product::products.section.collapse_all'
                                                      )
                                            "
                                            @click="
                                                toggleAccordions({
                                                    selector:
                                                        '.options-group .panel-heading',
                                                    state: isCollapsedOptionsAccordion,
                                                    data: form.options,
                                                })
                                            "
                                        >
                                            <i
                                                class="fa fa-angle-double-up"
                                                aria-hidden="true"
                                            ></i>
                                        </span>

                                        <div class="drag-handle">
                                            <i
                                                class="fa fa-ellipsis-h"
                                                aria-hidden="true"
                                            ></i>
                                            <i
                                                class="fa fa-ellipsis-h"
                                                aria-hidden="true"
                                            ></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="box-body clearfix">
                                    <div class="accordion-box-content">
                                        <draggable
                                            animation="150"
                                            class="options-group"
                                            force-fallback="true"
                                            item-key="uid"
                                            handle=".drag-handle"
                                            @start="disableContentSelection"
                                            @end="enableContentSelection"
                                            :list="form.options"
                                        >
                                            <template
                                                #item="{
                                                    element: option,
                                                    index,
                                                }"
                                            >
                                                <div
                                                    :id="`option-${option.uid}`"
                                                    class="content-accordion panel-group options-group-wrapper"
                                                    :class="`option-${option.uid}`"
                                                >
                                                    <div
                                                        class="panel panel-default option"
                                                    >
                                                        <div
                                                            class="panel-heading"
                                                            @click.stop="
                                                                toggleAccordion(
                                                                    $event,
                                                                    option
                                                                )
                                                            "
                                                        >
                                                            <h4
                                                                class="panel-title"
                                                            >
                                                                <div
                                                                    :aria-expanded="
                                                                        option.is_open
                                                                    "
                                                                    data-toggle="collapse"
                                                                    data-transition="false"
                                                                    :href="`#custom-collapse-${option.uid}`"
                                                                    :class="{
                                                                        collapsed:
                                                                            !option.is_open,
                                                                        'has-error':
                                                                            hasAnyError(
                                                                                {
                                                                                    name: 'options',
                                                                                    uid: option.uid,
                                                                                }
                                                                            ),
                                                                    }"
                                                                >
                                                                    <div
                                                                        class="d-flex align-items-center"
                                                                    >
                                                                        <span
                                                                            class="drag-handle"
                                                                        >
                                                                            <i
                                                                                class="fa"
                                                                                >&#xf142;</i
                                                                            >
                                                                            <i
                                                                                class="fa"
                                                                                >&#xf142;</i
                                                                            >
                                                                        </span>

                                                                        <span
                                                                            v-text="
                                                                                option.name ||
                                                                                trans(
                                                                                    'product::products.options.new_option'
                                                                                )
                                                                            "
                                                                        ></span>
                                                                    </div>

                                                                    <span
                                                                        class="delete-option"
                                                                        @click.stop="
                                                                            deleteOption(
                                                                                index,
                                                                                option.uid
                                                                            )
                                                                        "
                                                                    >
                                                                        <i
                                                                            class="fa fa-trash"
                                                                        ></i>
                                                                    </span>
                                                                </div>
                                                            </h4>
                                                        </div>

                                                        <div
                                                            class="panel-collapse"
                                                            :class="{
                                                                collapse:
                                                                    !option.is_open,
                                                            }"
                                                        >
                                                            <div
                                                                class="panel-body"
                                                            >
                                                                <div
                                                                    class="new-option"
                                                                >
                                                                    <div
                                                                        class="row"
                                                                    >
                                                                        <div
                                                                            class="col-sm-6"
                                                                        >
                                                                            <div
                                                                                class="form-group row"
                                                                            >
                                                                                <label
                                                                                    :for="`options-${option.uid}-name`"
                                                                                >
                                                                                    {{
                                                                                        trans(
                                                                                            "product::products.form.options.name"
                                                                                        )
                                                                                    }}
                                                                                    <span
                                                                                        v-if="
                                                                                            option.name ||
                                                                                            option.type
                                                                                        "
                                                                                        class="text-red"
                                                                                        >*</span
                                                                                    >
                                                                                </label>

                                                                                <input
                                                                                    type="text"
                                                                                    :name="`options.${option.uid}.name`"
                                                                                    class="form-control option-name-field"
                                                                                    :id="`options-${option.uid}-name`"
                                                                                    v-model="
                                                                                        option.name
                                                                                    "
                                                                                />

                                                                                <span
                                                                                    class="help-block text-red"
                                                                                    v-if="
                                                                                        errors.has(
                                                                                            `options.${option.uid}.name`
                                                                                        )
                                                                                    "
                                                                                    v-text="
                                                                                        errors.get(
                                                                                            `options.${option.uid}.name`
                                                                                        )
                                                                                    "
                                                                                >
                                                                                </span>
                                                                            </div>
                                                                        </div>

                                                                        <div
                                                                            class="col-sm-3"
                                                                        >
                                                                            <div
                                                                                class="form-group row"
                                                                            >
                                                                                <label
                                                                                    :for="`options-${option.uid}-type`"
                                                                                >
                                                                                    {{
                                                                                        trans(
                                                                                            "product::products.form.options.type"
                                                                                        )
                                                                                    }}
                                                                                    <span
                                                                                        v-if="
                                                                                            option.name ||
                                                                                            option.type
                                                                                        "
                                                                                        class="text-red"
                                                                                        >*</span
                                                                                    >
                                                                                </label>

                                                                                <select
                                                                                    :name="`options.${option.uid}.type`"
                                                                                    :id="`options-${option.uid}-type`"
                                                                                    class="form-control custom-select-black"
                                                                                    @change="
                                                                                        changeOptionType(
                                                                                            index,
                                                                                            option.uid
                                                                                        )
                                                                                    "
                                                                                    v-model="
                                                                                        option.type
                                                                                    "
                                                                                >
                                                                                    <option
                                                                                        value=""
                                                                                    >
                                                                                        {{
                                                                                            trans(
                                                                                                "product::products.form.options.option_types.please_select"
                                                                                            )
                                                                                        }}
                                                                                    </option>

                                                                                    <optgroup
                                                                                        :label="
                                                                                            trans(
                                                                                                'product::products.form.options.option_types.text'
                                                                                            )
                                                                                        "
                                                                                    >
                                                                                        <option
                                                                                            value="field"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.option_types.field"
                                                                                                )
                                                                                            }}
                                                                                        </option>

                                                                                        <option
                                                                                            value="textarea"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.option_types.textarea"
                                                                                                )
                                                                                            }}
                                                                                        </option>
                                                                                    </optgroup>

                                                                                    <optgroup
                                                                                        :label="
                                                                                            trans(
                                                                                                'product::products.form.options.option_types.select'
                                                                                            )
                                                                                        "
                                                                                    >
                                                                                        <option
                                                                                            value="dropdown"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.option_types.dropdown"
                                                                                                )
                                                                                            }}
                                                                                        </option>

                                                                                        <option
                                                                                            value="checkbox"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.option_types.checkbox"
                                                                                                )
                                                                                            }}
                                                                                        </option>

                                                                                        <option
                                                                                            value="checkbox_custom"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.option_types.checkbox_custom"
                                                                                                )
                                                                                            }}
                                                                                        </option>

                                                                                        <option
                                                                                            value="radio"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.option_types.radio"
                                                                                                )
                                                                                            }}
                                                                                        </option>

                                                                                        <option
                                                                                            value="radio_custom"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.option_types.radio_custom"
                                                                                                )
                                                                                            }}
                                                                                        </option>

                                                                                        <option
                                                                                            value="multiple_select"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.option_types.multiple_select"
                                                                                                )
                                                                                            }}
                                                                                        </option>
                                                                                    </optgroup>

                                                                                    <optgroup
                                                                                        :label="
                                                                                            trans(
                                                                                                'product::products.form.options.option_types.date'
                                                                                            )
                                                                                        "
                                                                                    >
                                                                                        <option
                                                                                            value="date"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.option_types.date"
                                                                                                )
                                                                                            }}
                                                                                        </option>

                                                                                        <option
                                                                                            value="date_time"
                                                                                            v-html="
                                                                                                trans(
                                                                                                    'product::products.form.options.option_types.date_time'
                                                                                                )
                                                                                            "
                                                                                        ></option>

                                                                                        <option
                                                                                            value="time"
                                                                                        >
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.option_types.time"
                                                                                                )
                                                                                            }}
                                                                                        </option>
                                                                                    </optgroup>
                                                                                </select>

                                                                                <span
                                                                                    class="help-block text-red"
                                                                                    v-if="
                                                                                        errors.has(
                                                                                            `options.${option.uid}.type`
                                                                                        )
                                                                                    "
                                                                                    v-text="
                                                                                        errors.get(
                                                                                            `options.${option.uid}.type`
                                                                                        )
                                                                                    "
                                                                                >
                                                                                </span>
                                                                            </div>
                                                                        </div>

                                                                        <div
                                                                            class="col-sm-3"
                                                                        >
                                                                            <div
                                                                                class="form-group row"
                                                                            >
                                                                                <div
                                                                                    class="checkbox"
                                                                                >
                                                                                    <input
                                                                                        type="checkbox"
                                                                                        :name="`options.${option.uid}.is_required`"
                                                                                        :id="`options-${option.uid}-is-required`"
                                                                                        class="form-control"
                                                                                        v-model="
                                                                                            option.is_required
                                                                                        "
                                                                                    />

                                                                                    <label
                                                                                        :for="`options-${option.uid}-is-required`"
                                                                                    >
                                                                                        {{
                                                                                            trans(
                                                                                                "product::products.form.options.is_required"
                                                                                            )
                                                                                        }}
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <template
                                                                    v-if="
                                                                        isOptionTypeText(
                                                                            option
                                                                        )
                                                                    "
                                                                >
                                                                    <div
                                                                        class="option-values"
                                                                        :id="`options.${option.uid}.values`"
                                                                    >
                                                                        <div
                                                                            class="table-responsive option-text"
                                                                        >
                                                                            <table
                                                                                class="table table-bordered"
                                                                            >
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.price"
                                                                                                )
                                                                                            }}
                                                                                        </th>
                                                                                        <th>
                                                                                            {{
                                                                                                trans(
                                                                                                    "product::products.form.options.price_type"
                                                                                                )
                                                                                            }}
                                                                                        </th>
                                                                                    </tr>
                                                                                </thead>

                                                                                <tbody>
                                                                                    <tr
                                                                                        v-for="(
                                                                                            value,
                                                                                            valueIndex
                                                                                        ) in option.values"
                                                                                        :key="
                                                                                            valueIndex
                                                                                        "
                                                                                    >
                                                                                        <td>
                                                                                            <div
                                                                                                class="input-group"
                                                                                            >
                                                                                                <span
                                                                                                    class="input-group-addon"
                                                                                                >
                                                                                                    {{
                                                                                                        value.price_type ===
                                                                                                        "fixed"
                                                                                                            ? defaultCurrencySymbol
                                                                                                            : "%"
                                                                                                    }}
                                                                                                </span>

                                                                                                <input
                                                                                                    type="number"
                                                                                                    min="0"
                                                                                                    step="0.1"
                                                                                                    :name="`options.${option.uid}.values.${value.uid}.price`"
                                                                                                    :id="`options-${option.uid}-values-${value.uid}-price`"
                                                                                                    class="form-control"
                                                                                                    @wheel="
                                                                                                        $event.target.blur()
                                                                                                    "
                                                                                                    v-model="
                                                                                                        value.price
                                                                                                    "
                                                                                                />
                                                                                            </div>

                                                                                            <span
                                                                                                class="help-block text-red"
                                                                                                v-if="
                                                                                                    errors.has(
                                                                                                        `options.${option.uid}.values.${value.uid}.price`
                                                                                                    )
                                                                                                "
                                                                                                v-text="
                                                                                                    errors.get(
                                                                                                        `options.${option.uid}.values.${value.uid}.price`
                                                                                                    )
                                                                                                "
                                                                                            >
                                                                                            </span>
                                                                                        </td>
                                                                                        <td>
                                                                                            <select
                                                                                                :name="`options.${option.uid}.values.${value.uid}.price_type`"
                                                                                                :id="`options-${option.uid}-values-${value.uid}-price-type`"
                                                                                                class="form-control custom-select-black"
                                                                                                v-model="
                                                                                                    value.price_type
                                                                                                "
                                                                                            >
                                                                                                <option
                                                                                                    value="fixed"
                                                                                                >
                                                                                                    {{
                                                                                                        trans(
                                                                                                            "product::products.form.options.price_types.fixed"
                                                                                                        )
                                                                                                    }}
                                                                                                </option>

                                                                                                <option
                                                                                                    value="percent"
                                                                                                >
                                                                                                    {{
                                                                                                        trans(
                                                                                                            "product::products.form.options.price_types.percent"
                                                                                                        )
                                                                                                    }}
                                                                                                </option>
                                                                                            </select>

                                                                                            <span
                                                                                                class="help-block text-red"
                                                                                                v-if="
                                                                                                    errors.has(
                                                                                                        `options.${option.uid}.values.${value.uid}.price_type`
                                                                                                    )
                                                                                                "
                                                                                                v-text="
                                                                                                    errors.get(
                                                                                                        `options.${option.uid}.values.${value.uid}.price_type`
                                                                                                    )
                                                                                                "
                                                                                            >
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </template>

                                                                <template
                                                                    v-else-if="
                                                                        isOptionTypeSelect(
                                                                            option
                                                                        )
                                                                    "
                                                                >
                                                                    <div
                                                                        class="option-values"
                                                                        :id="`options.${option.uid}.values`"
                                                                    >
                                                                        <div
                                                                            class="option-select"
                                                                        >
                                                                            <div
                                                                                class="table-responsive option-text"
                                                                            >
                                                                                <table
                                                                                    class="options table table-bordered"
                                                                                >
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th></th>
                                                                                            <th>
                                                                                                {{
                                                                                                    trans(
                                                                                                        "product::products.form.options.label"
                                                                                                    )
                                                                                                }}
                                                                                                <span
                                                                                                    class="text-red"
                                                                                                    >*</span
                                                                                                >
                                                                                            </th>
                                                                                            <th>
                                                                                                {{
                                                                                                    trans(
                                                                                                        "product::products.form.options.price"
                                                                                                    )
                                                                                                }}
                                                                                            </th>
                                                                                            <th>
                                                                                                {{
                                                                                                    trans(
                                                                                                        "product::products.form.options.price_type"
                                                                                                    )
                                                                                                }}
                                                                                            </th>
                                                                                            <th></th>
                                                                                        </tr>
                                                                                    </thead>

                                                                                    <tbody
                                                                                        is="vue:draggable"
                                                                                        animation="150"
                                                                                        handle=".drag-handle"
                                                                                        item-key="uid"
                                                                                        tag="tbody"
                                                                                        :list="
                                                                                            option.values
                                                                                        "
                                                                                    >
                                                                                        <template
                                                                                            #item="{
                                                                                                element:
                                                                                                    value,
                                                                                                index: valueIndex,
                                                                                            }"
                                                                                        >
                                                                                            <tr>
                                                                                                <td
                                                                                                    class="text-center"
                                                                                                >
                                                                                                    <span
                                                                                                        class="drag-handle"
                                                                                                    >
                                                                                                        <i
                                                                                                            class="fa"
                                                                                                            >&#xf142;</i
                                                                                                        >
                                                                                                        <i
                                                                                                            class="fa"
                                                                                                            >&#xf142;</i
                                                                                                        >
                                                                                                    </span>
                                                                                                </td>
                                                                                                <td>
                                                                                                    <input
                                                                                                        type="text"
                                                                                                        :name="`options.${option.uid}.values.${value.uid}.label`"
                                                                                                        :id="`options-${option.uid}-values-${value.uid}-label`"
                                                                                                        class="form-control"
                                                                                                        @keyup.enter="
                                                                                                            addOptionRowOnPressEnter(
                                                                                                                $event,
                                                                                                                index,
                                                                                                                valueIndex
                                                                                                            )
                                                                                                        "
                                                                                                        v-model="
                                                                                                            value.label
                                                                                                        "
                                                                                                    />

                                                                                                    <span
                                                                                                        class="help-block text-red"
                                                                                                        v-if="
                                                                                                            errors.has(
                                                                                                                `options.${option.uid}.values.${value.uid}.label`
                                                                                                            )
                                                                                                        "
                                                                                                        v-text="
                                                                                                            errors.get(
                                                                                                                `options.${option.uid}.values.${value.uid}.label`
                                                                                                            )
                                                                                                        "
                                                                                                    >
                                                                                                    </span>
                                                                                                </td>
                                                                                                <td>
                                                                                                    <div
                                                                                                        class="input-group"
                                                                                                    >
                                                                                                        <span
                                                                                                            class="input-group-addon"
                                                                                                        >
                                                                                                            {{
                                                                                                                value.price_type ===
                                                                                                                "fixed"
                                                                                                                    ? defaultCurrencySymbol
                                                                                                                    : "%"
                                                                                                            }}
                                                                                                        </span>

                                                                                                        <input
                                                                                                            type="number"
                                                                                                            min="0"
                                                                                                            step="0.1"
                                                                                                            :name="`options.${option.uid}.values.${value.uid}.price`"
                                                                                                            :id="`options-${option.uid}-values-${value.uid}-price`"
                                                                                                            class="form-control"
                                                                                                            @wheel="
                                                                                                                $event.target.blur()
                                                                                                            "
                                                                                                            v-model="
                                                                                                                value.price
                                                                                                            "
                                                                                                        />
                                                                                                    </div>

                                                                                                    <span
                                                                                                        class="help-block text-red"
                                                                                                        v-if="
                                                                                                            errors.has(
                                                                                                                `options.${option.uid}.values.${value.uid}.price`
                                                                                                            )
                                                                                                        "
                                                                                                        v-text="
                                                                                                            errors.get(
                                                                                                                `options.${option.uid}.values.${value.uid}.price`
                                                                                                            )
                                                                                                        "
                                                                                                    >
                                                                                                    </span>
                                                                                                </td>
                                                                                                <td>
                                                                                                    <select
                                                                                                        :name="`options.${option.uid}.values.${value.uid}.price_type`"
                                                                                                        :id="`options-${option.uid}-values-${value.uid}-price-type`"
                                                                                                        class="form-control custom-select-black"
                                                                                                        v-model="
                                                                                                            value.price_type
                                                                                                        "
                                                                                                    >
                                                                                                        <option
                                                                                                            value="fixed"
                                                                                                        >
                                                                                                            {{
                                                                                                                trans(
                                                                                                                    "product::products.form.options.price_types.fixed"
                                                                                                                )
                                                                                                            }}
                                                                                                        </option>

                                                                                                        <option
                                                                                                            value="percent"
                                                                                                        >
                                                                                                            {{
                                                                                                                trans(
                                                                                                                    "product::products.form.options.price_types.percent"
                                                                                                                )
                                                                                                            }}
                                                                                                        </option>
                                                                                                    </select>

                                                                                                    <span
                                                                                                        class="help-block text-red"
                                                                                                        v-if="
                                                                                                            errors.has(
                                                                                                                `options.${option.uid}.values.${value.uid}.price_type`
                                                                                                            )
                                                                                                        "
                                                                                                        v-text="
                                                                                                            errors.get(
                                                                                                                `options.${option.uid}.values.${value.uid}.price_type`
                                                                                                            )
                                                                                                        "
                                                                                                    >
                                                                                                    </span>
                                                                                                </td>
                                                                                                <td>
                                                                                                    <button
                                                                                                        type="button"
                                                                                                        tabindex="-1"
                                                                                                        class="btn btn-default delete-row"
                                                                                                        @click="
                                                                                                            deleteOptionRow(
                                                                                                                index,
                                                                                                                option.uid,
                                                                                                                valueIndex,
                                                                                                                value.uid
                                                                                                            )
                                                                                                        "
                                                                                                    >
                                                                                                        <i
                                                                                                            class="fa fa-trash"
                                                                                                        ></i>
                                                                                                    </button>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </template>
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>

                                                                            <button
                                                                                type="button"
                                                                                class="btn btn-default"
                                                                                @click="
                                                                                    addOptionRow(
                                                                                        index,
                                                                                        option.uid
                                                                                    )
                                                                                "
                                                                            >
                                                                                {{
                                                                                    trans(
                                                                                        "product::products.options.add_row"
                                                                                    )
                                                                                }}
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </draggable>

                                        <div class="accordion-box-footer">
                                            <button
                                                type="button"
                                                class="btn btn-default"
                                                @click="addOption"
                                            >
                                                {{
                                                    trans(
                                                        "product::products.options.add_option"
                                                    )
                                                }}
                                            </button>

                                            <div
                                                v-if="
                                                    hasAccess(
                                                        'admin.options.index'
                                                    )
                                                "
                                                class="insert-template"
                                            >
                                                <select
                                                    class="form-control custom-select-black"
                                                    v-model="globalOptionId"
                                                >
                                                    <option value="">
                                                        {{
                                                            trans(
                                                                "product::products.form.options.select_template"
                                                            )
                                                        }}
                                                    </option>

                                                    <option
                                                        v-for="globalOption in globalOptions"
                                                        :key="globalOption.id"
                                                        :value="globalOption.id"
                                                    >
                                                        {{ globalOption.name }}
                                                    </option>
                                                </select>

                                                <button
                                                    type="button"
                                                    class="btn btn-default"
                                                    :class="{
                                                        'btn-loading':
                                                            addingGlobalOption,
                                                    }"
                                                    :disabled="
                                                        isAddGlobalOptionDisabled
                                                    "
                                                    @click="addGlobalOption"
                                                >
                                                    {{
                                                        trans(
                                                            "product::products.options.insert"
                                                        )
                                                    }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template v-else-if="section === 'downloads'">
                                <div class="box-header">
                                    <h5>
                                        {{
                                            trans(
                                                "product::products.group.downloads"
                                            )
                                        }}
                                    </h5>

                                    <div class="drag-handle">
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                    </div>
                                </div>

                                <div class="box-body">
                                    <div
                                        class="product-downloads-wrapper clearfix"
                                    >
                                        <div class="table-responsive">
                                            <table
                                                class="options table table-bordered"
                                            >
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>
                                                            {{
                                                                trans(
                                                                    "product::products.downloads.file"
                                                                )
                                                            }}
                                                        </th>
                                                        <th></th>
                                                    </tr>
                                                </thead>

                                                <tbody
                                                    is="vue:draggable"
                                                    animation="150"
                                                    handle=".drag-handle"
                                                    item-key="uid"
                                                    tag="tbody"
                                                    :list="form.downloads"
                                                >
                                                    <template
                                                        #item="{
                                                            element: download,
                                                            index,
                                                        }"
                                                    >
                                                        <tr>
                                                            <td
                                                                class="text-center"
                                                            >
                                                                <span
                                                                    class="drag-handle"
                                                                >
                                                                    <i
                                                                        class="fa"
                                                                        >&#xf142;</i
                                                                    >
                                                                    <i
                                                                        class="fa"
                                                                        >&#xf142;</i
                                                                    >
                                                                </span>
                                                            </td>

                                                            <td>
                                                                <div
                                                                    class="choose-file-group"
                                                                >
                                                                    <input
                                                                        type="text"
                                                                        :value="
                                                                            download.filename
                                                                        "
                                                                        class="form-control downloadable-file-name"
                                                                        readonly
                                                                    />

                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-default btn-choose-file"
                                                                        @click="
                                                                            chooseDownloadableFile(
                                                                                index
                                                                            )
                                                                        "
                                                                    >
                                                                        {{
                                                                            trans(
                                                                                "product::products.downloads.choose"
                                                                            )
                                                                        }}
                                                                    </button>
                                                                </div>
                                                            </td>

                                                            <td
                                                                class="text-center"
                                                            >
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-default delete-row"
                                                                    @click="
                                                                        deleteDownload(
                                                                            index
                                                                        )
                                                                    "
                                                                >
                                                                    <i
                                                                        class="fa fa-trash"
                                                                    ></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>

                                        <button
                                            type="button"
                                            class="btn btn-default"
                                            @click="addDownload"
                                        >
                                            {{
                                                trans(
                                                    "product::products.downloads.add_file"
                                                )
                                            }}
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </draggable>
            </div>

            <div class="product-form-right-column col-lg-4 col-md-12">
                <draggable
                    animation="150"
                    class="product-form-column"
                    :class="{ dragging: isRightColumnSectionDragging }"
                    data-name="product-form-right-sections"
                    force-fallback="true"
                    item-key="element"
                    handle=".drag-handle"
                    :list="sections['product-form-right-sections']"
                    :store="storeSections"
                    @start="disableContentSelection"
                    @end="enableContentSelection"
                    @choose="isRightColumnSectionDragging = true"
                    @unchoose="isRightColumnSectionDragging = false"
                    @change="notifySectionOrderChange"
                >
                    <template #item="{ element: section }">
                        <div class="box">
                            <template v-if="section === 'media'">
                                <div class="box-header">
                                    <h5>
                                        {{
                                            trans(
                                                "product::products.group.media"
                                            )
                                        }}
                                    </h5>

                                    <div class="drag-handle">
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                    </div>
                                </div>

                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="">
                                                <draggable
                                                    class="product-media-grid"
                                                    animation="200"
                                                    item-key="index"
                                                    handle=".handle"
                                                    :move="preventLastSlideDrag"
                                                    :list="form.media"
                                                >
                                                    <template
                                                        #item="{
                                                            element: media,
                                                            index,
                                                        }"
                                                    >
                                                        <div
                                                            class="media-grid-item handle"
                                                        >
                                                            <div
                                                                class="image-holder"
                                                            >
                                                                <img
                                                                    :src="
                                                                        media.path
                                                                    "
                                                                    alt="product media"
                                                                />

                                                                <button
                                                                    type="button"
                                                                    class="btn remove-image"
                                                                    @click="
                                                                        removeMedia(
                                                                            index
                                                                        )
                                                                    "
                                                                >
                                                                    <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        width="24"
                                                                        height="24"
                                                                        viewBox="0 0 24 24"
                                                                        fill="none"
                                                                    >
                                                                        <path
                                                                            d="M6.00098 17.9995L17.9999 6.00053"
                                                                            stroke="#292D32"
                                                                            stroke-width="1.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                        />
                                                                        <path
                                                                            d="M17.9999 17.9995L6.00098 6.00055"
                                                                            stroke="#292D32"
                                                                            stroke-width="1.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                        />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    <template #footer>
                                                        <div
                                                            class="media-grid-item media-picker disabled"
                                                            @click="addMedia"
                                                        >
                                                            <div
                                                                class="image-holder"
                                                            >
                                                                <img
                                                                    src="../../../../../../Admin/Resources/assets/images/placeholder_image.png"
                                                                    class="placeholder-image"
                                                                    alt="Placeholder Image"
                                                                />
                                                            </div>
                                                        </div>
                                                    </template>
                                                </draggable>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template v-if="section === 'price'">
                                <div class="box-header">
                                    <h5>
                                        {{
                                            trans(
                                                "product::products.group.pricing"
                                            )
                                        }}
                                    </h5>

                                    <div class="drag-handle">
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                    </div>
                                </div>

                                <div class="box-body">
                                    <div
                                        v-if="hasAnyVariant"
                                        class="alert alert-info"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                        >
                                            <path
                                                d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM11.25 8C11.25 7.59 11.59 7.25 12 7.25C12.41 7.25 12.75 7.59 12.75 8V13C12.75 13.41 12.41 13.75 12 13.75C11.59 13.75 11.25 13.41 11.25 13V8ZM12.92 16.38C12.87 16.51 12.8 16.61 12.71 16.71C12.61 16.8 12.5 16.87 12.38 16.92C12.26 16.97 12.13 17 12 17C11.87 17 11.74 16.97 11.62 16.92C11.5 16.87 11.39 16.8 11.29 16.71C11.2 16.61 11.13 16.51 11.08 16.38C11.03 16.26 11 16.13 11 16C11 15.87 11.03 15.74 11.08 15.62C11.13 15.5 11.2 15.39 11.29 15.29C11.39 15.2 11.5 15.13 11.62 15.08C11.86 14.98 12.14 14.98 12.38 15.08C12.5 15.13 12.61 15.2 12.71 15.29C12.8 15.39 12.87 15.5 12.92 15.62C12.97 15.74 13 15.87 13 16C13 16.13 12.97 16.26 12.92 16.38Z"
                                                fill="#555555"
                                            />
                                        </svg>

                                        <span>{{
                                            trans(
                                                "product::products.variants.has_product_variant"
                                            )
                                        }}</span>
                                    </div>

                                    <template v-else>
                                        <div class="form-group row">
                                            <label
                                                for="price"
                                                class="col-sm-12 control-label text-left"
                                            >
                                                {{
                                                    trans(
                                                        "product::attributes.price"
                                                    )
                                                }}
                                                <span class="text-red">*</span>
                                            </label>

                                            <div class="col-sm-12">
                                                <div class="input-group">
                                                    <span
                                                        class="input-group-addon"
                                                    >
                                                        {{
                                                            defaultCurrencySymbol
                                                        }}
                                                    </span>

                                                    <input
                                                        type="number"
                                                        min="0"
                                                        name="price"
                                                        step="0.1"
                                                        id="price"
                                                        class="form-control"
                                                        @wheel="
                                                            $event.target.blur()
                                                        "
                                                        v-model="form.price"
                                                    />
                                                </div>

                                                <span
                                                    class="help-block text-red"
                                                    v-if="errors.has('price')"
                                                    v-text="errors.get('price')"
                                                ></span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label
                                                for="special-price"
                                                class="col-sm-12 control-label text-left"
                                            >
                                                {{
                                                    trans(
                                                        "product::attributes.special_price"
                                                    )
                                                }}
                                            </label>

                                            <div class="col-sm-12">
                                                <div class="input-group">
                                                    <span
                                                        class="input-group-addon"
                                                    >
                                                        {{
                                                            form.special_price_type ===
                                                            "fixed"
                                                                ? defaultCurrencySymbol
                                                                : "%"
                                                        }}
                                                    </span>

                                                    <input
                                                        type="number"
                                                        min="0"
                                                        name="special_price"
                                                        step="0.1"
                                                        id="special-price"
                                                        class="form-control"
                                                        @wheel="
                                                            $event.target.blur()
                                                        "
                                                        v-model="
                                                            form.special_price
                                                        "
                                                    />
                                                </div>

                                                <span
                                                    class="help-block text-red"
                                                    v-if="
                                                        errors.has(
                                                            'special_price'
                                                        )
                                                    "
                                                    v-text="
                                                        errors.get(
                                                            'special_price'
                                                        )
                                                    "
                                                ></span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label
                                                for="special-price-type"
                                                class="col-sm-12 control-label text-left"
                                            >
                                                {{
                                                    trans(
                                                        "product::attributes.special_price_type"
                                                    )
                                                }}
                                            </label>

                                            <div class="col-sm-12">
                                                <select
                                                    name="special_price_type"
                                                    id="special-price-type"
                                                    class="form-control custom-select-black"
                                                    v-model="
                                                        form.special_price_type
                                                    "
                                                >
                                                    <option value="fixed">
                                                        {{
                                                            trans(
                                                                "product::products.form.special_price_types.fixed"
                                                            )
                                                        }}
                                                    </option>

                                                    <option value="percent">
                                                        {{
                                                            trans(
                                                                "product::products.form.special_price_types.percent"
                                                            )
                                                        }}
                                                    </option>
                                                </select>

                                                <span
                                                    class="help-block text-red"
                                                    v-if="
                                                        errors.has(
                                                            'special_price_type'
                                                        )
                                                    "
                                                    v-text="
                                                        errors.get(
                                                            'special_price_type'
                                                        )
                                                    "
                                                ></span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label
                                                for="special-price-start"
                                                class="col-sm-12 control-label text-left"
                                            >
                                                {{
                                                    trans(
                                                        "product::attributes.special_price_start"
                                                    )
                                                }}
                                            </label>

                                            <div class="col-sm-12">
                                                <div class="input-group">
                                                    <span
                                                        class="input-group-addon"
                                                    >
                                                        <i
                                                            class="fa fa-calendar"
                                                            aria-hidden="true"
                                                        ></i>
                                                    </span>

                                                    <flat-pickr
                                                        name="special_price_start"
                                                        id="special-price-start"
                                                        class="form-control"
                                                        :config="
                                                            flatPickrConfig
                                                        "
                                                        v-model="
                                                            form.special_price_start
                                                        "
                                                    >
                                                    </flat-pickr>

                                                    <span
                                                        class="input-group-addon cursor-pointer"
                                                        v-if="
                                                            form.special_price_start
                                                        "
                                                        @click="
                                                            removeDatePickerValue(
                                                                'special_price_start'
                                                            )
                                                        "
                                                    >
                                                        <i
                                                            class="fa fa-times"
                                                            aria-hidden="true"
                                                        ></i>
                                                    </span>
                                                </div>

                                                <span
                                                    class="help-block text-red"
                                                    v-if="
                                                        errors.has(
                                                            'special_price_start'
                                                        )
                                                    "
                                                    v-text="
                                                        errors.get(
                                                            'special_price_start'
                                                        )
                                                    "
                                                ></span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label
                                                for="special-price-end"
                                                class="col-sm-12 control-label text-left"
                                            >
                                                {{
                                                    trans(
                                                        "product::attributes.special_price_end"
                                                    )
                                                }}
                                            </label>

                                            <div class="col-sm-12">
                                                <div class="input-group">
                                                    <span
                                                        class="input-group-addon"
                                                    >
                                                        <i
                                                            class="fa fa-calendar"
                                                            aria-hidden="true"
                                                        ></i>
                                                    </span>

                                                    <flat-pickr
                                                        name="special_price_end"
                                                        id="special-price-end"
                                                        class="form-control"
                                                        :config="
                                                            flatPickrConfig
                                                        "
                                                        v-model="
                                                            form.special_price_end
                                                        "
                                                    >
                                                    </flat-pickr>

                                                    <span
                                                        class="input-group-addon cursor-pointer"
                                                        v-if="
                                                            form.special_price_end
                                                        "
                                                        @click="
                                                            removeDatePickerValue(
                                                                'special_price_end'
                                                            )
                                                        "
                                                    >
                                                        <i
                                                            class="fa fa-times"
                                                            aria-hidden="true"
                                                        ></i>
                                                    </span>
                                                </div>

                                                <span
                                                    class="help-block text-red"
                                                    v-if="
                                                        errors.has(
                                                            'special_price_end'
                                                        )
                                                    "
                                                    v-text="
                                                        errors.get(
                                                            'special_price_end'
                                                        )
                                                    "
                                                ></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template v-else-if="section === 'inventory'">
                                <div class="box-header">
                                    <h5>
                                        {{
                                            trans(
                                                "product::products.group.inventory"
                                            )
                                        }}
                                    </h5>

                                    <div class="drag-handle">
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                    </div>
                                </div>

                                <div class="box-body">
                                    <div
                                        v-if="hasAnyVariant"
                                        class="alert alert-info"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                        >
                                            <path
                                                d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM11.25 8C11.25 7.59 11.59 7.25 12 7.25C12.41 7.25 12.75 7.59 12.75 8V13C12.75 13.41 12.41 13.75 12 13.75C11.59 13.75 11.25 13.41 11.25 13V8ZM12.92 16.38C12.87 16.51 12.8 16.61 12.71 16.71C12.61 16.8 12.5 16.87 12.38 16.92C12.26 16.97 12.13 17 12 17C11.87 17 11.74 16.97 11.62 16.92C11.5 16.87 11.39 16.8 11.29 16.71C11.2 16.61 11.13 16.51 11.08 16.38C11.03 16.26 11 16.13 11 16C11 15.87 11.03 15.74 11.08 15.62C11.13 15.5 11.2 15.39 11.29 15.29C11.39 15.2 11.5 15.13 11.62 15.08C11.86 14.98 12.14 14.98 12.38 15.08C12.5 15.13 12.61 15.2 12.71 15.29C12.8 15.39 12.87 15.5 12.92 15.62C12.97 15.74 13 15.87 13 16C13 16.13 12.97 16.26 12.92 16.38Z"
                                                fill="#555555"
                                            />
                                        </svg>

                                        <span>
                                            {{
                                                trans(
                                                    "product::products.variants.has_product_variant"
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <template v-else>
                                        <div class="form-group row">
                                            <label
                                                for="sku"
                                                class="col-sm-12 control-label text-left"
                                            >
                                                {{
                                                    trans(
                                                        "product::attributes.sku"
                                                    )
                                                }}
                                            </label>

                                            <div class="col-sm-12">
                                                <input
                                                    type="text"
                                                    name="sku"
                                                    id="sku"
                                                    class="form-control"
                                                    v-model="form.sku"
                                                />

                                                <span
                                                    class="help-block text-red"
                                                    v-if="errors.has('sku')"
                                                    v-text="errors.get('sku')"
                                                ></span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label
                                                for="manage-stock"
                                                class="col-sm-12 control-label text-left"
                                            >
                                                {{
                                                    trans(
                                                        "product::attributes.manage_stock"
                                                    )
                                                }}
                                            </label>

                                            <div class="col-sm-12">
                                                <select
                                                    name="manage_stock"
                                                    id="manage-stock"
                                                    class="form-control custom-select-black"
                                                    @change="
                                                        focusField({
                                                            selector: '#qty',
                                                        })
                                                    "
                                                    v-model.number="
                                                        form.manage_stock
                                                    "
                                                >
                                                    <option
                                                        value="0"
                                                        v-html="
                                                            trans(
                                                                'product::products.form.manage_stock_states.0'
                                                            )
                                                        "
                                                    ></option>
                                                    <option value="1">
                                                        {{
                                                            trans(
                                                                "product::products.form.manage_stock_states.1"
                                                            )
                                                        }}
                                                    </option>
                                                </select>

                                                <span
                                                    class="help-block text-red"
                                                    v-if="
                                                        errors.has(
                                                            'manage_stock'
                                                        )
                                                    "
                                                    v-text="
                                                        errors.get(
                                                            'manage_stock'
                                                        )
                                                    "
                                                ></span>
                                            </div>
                                        </div>

                                        <div
                                            class="form-group row"
                                            v-if="form.manage_stock == 1"
                                        >
                                            <label
                                                for="qty"
                                                class="col-sm-12 control-label text-left"
                                            >
                                                {{
                                                    trans(
                                                        "product::attributes.qty"
                                                    )
                                                }}
                                                <span class="text-red">*</span>
                                            </label>

                                            <div class="col-sm-12">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    name="qty"
                                                    step="1"
                                                    id="qty"
                                                    class="form-control"
                                                    @wheel="
                                                        $event.target.blur()
                                                    "
                                                    v-model.number="form.qty"
                                                />

                                                <span
                                                    class="help-block text-red"
                                                    v-if="errors.has('qty')"
                                                    v-text="errors.get('qty')"
                                                ></span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label
                                                for="in-stock"
                                                class="col-sm-12 control-label text-left"
                                            >
                                                {{
                                                    trans(
                                                        "product::attributes.in_stock"
                                                    )
                                                }}
                                            </label>

                                            <div class="col-sm-12">
                                                <select
                                                    name="in_stock"
                                                    id="in-stock"
                                                    class="form-control custom-select-black"
                                                    v-model.number="
                                                        form.in_stock
                                                    "
                                                >
                                                    <option value="1">
                                                        {{
                                                            trans(
                                                                "product::products.form.stock_availability_states.1"
                                                            )
                                                        }}
                                                    </option>
                                                    <option value="0">
                                                        {{
                                                            trans(
                                                                "product::products.form.stock_availability_states.0"
                                                            )
                                                        }}
                                                    </option>
                                                </select>

                                                <span
                                                    class="help-block text-red"
                                                    v-if="
                                                        errors.has('in_stock')
                                                    "
                                                    v-text="
                                                        errors.get('in_stock')
                                                    "
                                                ></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template v-else-if="section === 'seo'">
                                <div class="box-header">
                                    <h5>
                                        {{
                                            trans("product::products.group.seo")
                                        }}
                                    </h5>

                                    <div class="drag-handle">
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                    </div>
                                </div>

                                <div class="box-body">
                                    <div class="form-group row">
                                        <label
                                            for="slug"
                                            class="col-sm-12 control-label text-left"
                                        >
                                            {{
                                                trans(
                                                    "product::attributes.slug"
                                                )
                                            }}

                                            <span
                                                v-if="
                                                    window.location.pathname.endsWith(
                                                        '/edit'
                                                    )
                                                "
                                                class="text-red"
                                                >*</span
                                            >
                                        </label>

                                        <div class="col-sm-12">
                                            <input
                                                type="text"
                                                name="slug"
                                                id="slug"
                                                class="form-control"
                                                @change="
                                                    setProductSlug(
                                                        $event.target.value
                                                    )
                                                "
                                                v-model="form.slug"
                                            />

                                            <span
                                                class="help-block text-red"
                                                v-if="errors.has('slug')"
                                                v-text="errors.get('slug')"
                                            ></span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label
                                            for="meta-title"
                                            class="col-sm-12 control-label text-left"
                                        >
                                            {{
                                                trans(
                                                    "meta::attributes.meta_title"
                                                )
                                            }}
                                        </label>

                                        <div class="col-sm-12">
                                            <input
                                                type="text"
                                                name="meta.meta_title"
                                                id="meta-title"
                                                class="form-control"
                                                v-model="form.meta.meta_title"
                                            />

                                            <span
                                                class="help-block text-red"
                                                v-if="
                                                    errors.has(
                                                        'meta.meta_title'
                                                    )
                                                "
                                                v-text="
                                                    errors.get(
                                                        'meta.meta_title'
                                                    )
                                                "
                                            ></span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label
                                            for="meta-description"
                                            class="col-sm-12 control-label text-left"
                                        >
                                            {{
                                                trans(
                                                    "meta::attributes.meta_description"
                                                )
                                            }}
                                        </label>

                                        <div class="col-sm-12">
                                            <textarea
                                                name="meta.meta_description"
                                                rows="6"
                                                cols="10"
                                                id="meta-description"
                                                class="form-control"
                                                v-model="
                                                    form.meta.meta_description
                                                "
                                            ></textarea>

                                            <span
                                                class="help-block text-red"
                                                v-if="
                                                    errors.has(
                                                        'meta.meta_description'
                                                    )
                                                "
                                                v-text="
                                                    errors.get(
                                                        'meta.meta_description'
                                                    )
                                                "
                                            ></span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template v-else-if="section === 'additional'">
                                <div class="box-header">
                                    <h5>
                                        {{
                                            trans(
                                                "product::products.group.additional"
                                            )
                                        }}
                                    </h5>

                                    <div class="drag-handle">
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                        <i
                                            class="fa fa-ellipsis-h"
                                            aria-hidden="true"
                                        ></i>
                                    </div>
                                </div>

                                <div class="box-body">
                                    <div class="form-group row">
                                        <label
                                            for="short-description"
                                            class="col-sm-12 control-label text-left"
                                        >
                                            {{
                                                trans(
                                                    "product::attributes.short_description"
                                                )
                                            }}
                                        </label>

                                        <div class="col-sm-12">
                                            <textarea
                                                name="short_description"
                                                rows="6"
                                                cols="10"
                                                id="short-description"
                                                class="form-control"
                                                v-model="form.short_description"
                                            ></textarea>

                                            <span
                                                class="help-block text-red"
                                                v-if="
                                                    errors.has(
                                                        'short_description'
                                                    )
                                                "
                                                v-text="
                                                    errors.get(
                                                        'short_description'
                                                    )
                                                "
                                            ></span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label
                                            for="new-from"
                                            class="col-sm-12 control-label text-left"
                                        >
                                            {{
                                                trans(
                                                    "product::attributes.new_from"
                                                )
                                            }}
                                        </label>

                                        <div class="col-sm-12">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i
                                                        class="fa fa-calendar"
                                                        aria-hidden="true"
                                                    ></i>
                                                </span>

                                                <flat-pickr
                                                    name="new_from"
                                                    id="new-from"
                                                    class="form-control"
                                                    :config="flatPickrConfig"
                                                    v-model="form.new_from"
                                                >
                                                </flat-pickr>

                                                <span
                                                    class="input-group-addon cursor-pointer"
                                                    v-if="form.new_from"
                                                    @click="
                                                        removeDatePickerValue(
                                                            'new_from'
                                                        )
                                                    "
                                                >
                                                    <i
                                                        class="fa fa-times"
                                                        aria-hidden="true"
                                                    ></i>
                                                </span>
                                            </div>

                                            <span
                                                class="help-block text-red"
                                                v-if="errors.has('new_from')"
                                                v-text="errors.get('new_from')"
                                            ></span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label
                                            for="new-to"
                                            class="col-sm-12 control-label text-left"
                                        >
                                            {{
                                                trans(
                                                    "product::attributes.new_to"
                                                )
                                            }}
                                        </label>

                                        <div class="col-sm-12">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i
                                                        class="fa fa-calendar"
                                                        aria-hidden="true"
                                                    ></i>
                                                </span>

                                                <flat-pickr
                                                    name="new_to"
                                                    id="new-to"
                                                    class="form-control"
                                                    :config="flatPickrConfig"
                                                    v-model="form.new_to"
                                                >
                                                </flat-pickr>

                                                <span
                                                    class="input-group-addon cursor-pointer"
                                                    v-if="form.new_to"
                                                    @click="
                                                        removeDatePickerValue(
                                                            'new_to'
                                                        )
                                                    "
                                                >
                                                    <i
                                                        class="fa fa-times"
                                                        aria-hidden="true"
                                                    ></i>
                                                </span>
                                            </div>

                                            <span
                                                class="help-block text-red"
                                                v-if="errors.has('new_to')"
                                                v-text="errors.get('new_to')"
                                            ></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </draggable>

                <div class="box">
                    <div class="box-header">
                        <h5>
                            {{
                                trans("product::products.group.linked_products")
                            }}
                        </h5>
                    </div>

                    <div class="box-body">
                        <div class="form-group row">
                            <label
                                for="up-sells"
                                class="col-sm-12 control-label text-left"
                            >
                                {{ trans("product::attributes.up_sells") }}
                            </label>

                            <div class="col-sm-12">
                                <select
                                    name="up_sells"
                                    id="up-sells"
                                    multiple
                                    v-model="form.up_sells"
                                    ref="upSellProducts"
                                ></select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label
                                for="cross-sells"
                                class="col-sm-12 control-label text-left"
                            >
                                {{ trans("product::attributes.cross_sells") }}
                            </label>

                            <div class="col-sm-12">
                                <select
                                    name="cross_sells"
                                    id="cross-sells"
                                    multiple
                                    v-model="form.cross_sells"
                                    ref="crossSellProducts"
                                ></select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label
                                for="related-products"
                                class="col-sm-12 control-label text-left"
                            >
                                {{
                                    trans(
                                        "product::attributes.related_products"
                                    )
                                }}
                            </label>

                            <div class="col-sm-12">
                                <select
                                    name="related_products"
                                    id="related-products"
                                    multiple
                                    v-model="form.related_products"
                                    ref="relatedProducts"
                                ></select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-form-footer">
            <button
                type="button"
                class="btn btn-default"
                :class="{ 'btn-loading': formSubmissionType === 'save' }"
                :disabled="formSubmissionType"
                @click="submit({ submissionType: 'save' })"
            >
                {{ trans("product::products.save") }}
            </button>

            <button
                type="button"
                class="btn btn-primary"
                :class="{
                    'btn-loading': formSubmissionType === 'save_and_exit',
                }"
                :disabled="formSubmissionType"
                @click="submit({ submissionType: 'save_and_exit' })"
                v-html="trans('product::products.save_and_exit')"
            ></button>
        </div>
    </form>
</template>

<script>
import draggable from "vuedraggable";
import ProductMixin from "../mixins/ProductMixin";
import Errors from "@admin/js/Errors";

export default {
    components: {
        draggable,
    },

    mixins: [ProductMixin],

    data: {
        brands: FleetCart.data["brands"] ?? {},
        categories: FleetCart.data["categories"] ?? {},
        taxClasses: FleetCart.data["tax-classes"] ?? {},
        tags: FleetCart.data["tags"] ?? {},
        attributeSets: FleetCart.data["attribute-sets"] ?? {},
        globalVariations: FleetCart.data["global-variations"] ?? [],
        globalOptions: FleetCart.data["global-options"] ?? [],
        formSubmissionType: null,
        form: {
            brand_id: "",
            tax_class_id: "",
            is_active: true,
            media: [],
            is_virtual: false,
            manage_stock: 0,
            in_stock: 1,
            special_price_type: "fixed",
            meta: {},
            attributes: [],
            downloads: [],
            variations: [],
            variants: [],
            options: [],
            slug: null,
        },
        errors: new Errors(),
        searchableSelectizeConfig: {},
        flatPickrConfig: {
            mode: "single",
            enableTime: true,
            altInput: true,
        },
    },

    created() {
        this.setFormData();
        this.setSearchableSelectizeConfig();
        this.setDefaultVariantUid();
        this.setVariantsLength();
    },

    mounted() {
        this.hideAlertExitFlash();
        this.initAllAttributeValuesSelectize();
    },

    methods: {
        prepareFormData(formData) {
            this.prepareAttributes(formData.attributes);
            this.prepareVariations(formData.variations);
            this.prepareVariants(formData.variants);
            this.prepareOptions(formData.options);

            return formData;
        },

        setFormData() {
            this.form = { ...this.prepareFormData(FleetCart.data["product"]) };
        },

        destroyAllAttributeValuesSelectize() {
            this.form.attributes.forEach((attribute) => {
                $(`#attributes-${attribute.uid}-values`)[0].selectize.destroy();
            });
        },

        setDefaultVariantUid() {
            if (this.hasAnyVariant) {
                this.defaultVariantUid = this.form.variants.find(
                    ({ is_default }) => is_default === true
                ).uid;
            }
        },

        setVariantsLength() {
            this.variantsLength = this.form.variants.length;
        },

        hideAlertExitFlash() {
            const alertExitFlash = $(".alert-exit-flash");

            if (alertExitFlash.length !== 0) {
                setTimeout(() => {
                    alertExitFlash.remove();
                }, 3000);
            }
        },

        submit({ submissionType }) {
            this.formSubmissionType = submissionType;

            axios
                .put(
                    `/products/${this.form.id}`,
                    this.transformData(this.form),
                    {
                        params: {
                            ...(submissionType === "save_and_exit" && {
                                exit_flash: true,
                            }),
                        },
                    }
                )
                .then(({ data }) => {
                    if (submissionType === "save_and_exit") {
                        location.href = "/admin/products";

                        return;
                    }

                    this.destroyAllAttributeValuesSelectize();

                    this.form = { ...data.product_resource };

                    this.errors.reset();
                    this.prepareFormData(this.form);
                    this.resetBulkEditVariantFields();

                    if (this.hasAnyVariant) {
                        this.setVariantsName();
                    }

                    this.$nextTick(() => {
                        this.initAllAttributeValuesSelectize();
                        this.initColorPicker();
                    });

                    toaster(data.message, {
                        type: "success",
                    });
                })
                .catch(({ response }) => {
                    this.formSubmissionType = null;

                    toaster(response.data.message, {
                        type: "default",
                    });

                    if (response.status === 422) {
                        this.errors.reset();
                        this.errors.record(response.data.errors);
                        this.focusFirstErrorField(this.$refs.form.elements);

                        return;
                    }

                    if (this.hasAnyVariant) {
                        this.regenerateVariationsAndVariantsUid();
                    }
                })
                .finally(() => {
                    if (submissionType === "save") {
                        this.formSubmissionType = null;
                    }
                });
        },
    },
};
</script>
