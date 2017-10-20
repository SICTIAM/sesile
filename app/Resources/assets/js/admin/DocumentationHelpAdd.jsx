import React, { Component } from 'react'
import { func, object } from 'prop-types'
import { translate } from 'react-i18next'
import Validator from 'validatorjs'
import InputValidation from "../_components/InputValidation"
import {Button, InputDatePicker} from '../_components/Form'
import Moment from 'moment'

class DocumentationHelpAdd extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
        this.state = {
            help: {
                description: "",
                date: {}
            },
            editState: false
        }
    }

    validationRules = {
        description: 'required',
        date: 'required'
    }

    handleChange = (name, value) => {
        const { help } = this.state
        help[name] = value
        this.setState({help})
        this.formIsValid(help)
    }

    handleChangeDate = (date) => {
        const { help } = this.state
        help['date'] = date
        this.setState({help})
        this.formIsValid(help)
    }

    formIsValid = (fields) => {
        const validation = new Validator(fields, this.validationRules)
        this.setState({editState: validation.passes()})

        return validation.passes()
    }

    addHelp = () => {
        if(this.formIsValid(this.state.help)) this.props.addHelp(this.state.help)
    }


    render() {

        const { help, editState } = this.state
        const { t } = this.context
        const { i18nextLng } = window.localStorage
        Moment.locale(i18nextLng)

        return (
            <div className="grid-x grid-padding-x grid-padding-y">
                <InputValidation    id="description"
                                    type="text"
                                    className={"medium-4 cell"}
                                    labelText={t('common.label.description')}
                                    value={ help.description }
                                    onChange={this.handleChange}
                                    validationRule={this.validationRules.description}
                                    placeholder={t('admin.documentations.placeholder_description')}
                />

                <InputDatePicker
                    label={ t('admin.documentations.label_date') }
                    date={ Moment(help.date) }
                    onChange={ this.handleChangeDate }
                    locale={ i18nextLng }
                    className="medium-4 cell"
                />


                <Button id="submit-infos"
                        className="cell medium-4 text-right"
                        classNameButton=""
                        onClick={ this.addHelp }
                        labelText={t('common.button.save')}
                        disabled={!editState}
                />
            </div>
        )
    }

}

DocumentationHelpAdd.PropTypes = {
    addHelp: func.isRequired
}

export default translate(['sesile'])(DocumentationHelpAdd)