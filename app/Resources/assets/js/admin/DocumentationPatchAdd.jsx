import React, { Component } from 'react'
import { func, object } from 'prop-types'
import { translate } from 'react-i18next'
import Validator from 'validatorjs'
import InputValidation from "../_components/InputValidation"
import {Button, InputDatePicker} from '../_components/Form'
import Moment from 'moment'

class DocumentationPatchAdd extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
        this.state = {
            patch: {
                description: "",
                version: "",
                date: {}
            },
            editState: false
        }
    }

    validationRules = {
        description: 'required',
        version: 'required',
        date: 'required'
    }


    handleChange = (name, value) => {
        const { patch } = this.state
        patch[name] = value
        this.setState({patch})

        this.formIsValid(patch)
    }

    handleChangeDate = (date) => {
        const { patch } = this.state
        patch['date'] = date
        this.setState({patch})
        this.formIsValid(patch)
    }

    formIsValid = (fields) => {
        const validation = new Validator(fields, this.validationRules)
        this.setState({editState: validation.passes()})

        return validation.passes()
    }

    addPatch = () => {
        if(this.formIsValid(this.state.patch)) this.props.addPatch(this.state.patch)
    }


    render() {

        const { patch, editState } = this.state
        const { t } = this.context
        const { i18nextLng } = window.localStorage
        Moment.locale(i18nextLng)

        return (
            <div className="grid-x grid-padding-x grid-padding-y">
                <InputValidation    id="description"
                                    type="text"
                                    className={"medium-3 cell"}
                                    labelText={t('common.label.description')}
                                    value={ patch.description }
                                    onChange={this.handleChange}
                                    validationRule={this.validationRules.description}
                                    placeholder={t('admin.documentations.placeholder_description')}
                />

                <InputValidation    id="version"
                                    type="text"
                                    className={"medium-2 cell"}
                                    labelText={t('admin.documentations.label_version')}
                                    value={ patch.version }
                                    onChange={this.handleChange}
                                    validationRule={this.validationRules.version}
                                    placeholder={t('admin.documentations.placeholder_version')}
                />

                <InputDatePicker
                    label={ t('admin.documentations.label_date') }
                    date={ Moment(patch.date) }
                    onChange={ this.handleChangeDate }
                    locale={ i18nextLng }
                    className="medium-3 cell"
                />


                <Button id="submit-infos"
                        className="cell medium-4 text-right"
                        classNameButton=""
                        onClick={ this.addPatch }
                        labelText={t('common.button.save')}
                        disabled={!editState}
                />
            </div>
        )
    }

}

DocumentationPatchAdd.PropTypes = {
    addPatch: func.isRequired
}

export default translate(['sesile'])(DocumentationPatchAdd)