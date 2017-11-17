import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import Moment from 'moment'
import Validator from 'validatorjs'
import { Button } from '../_components/Form'
import InputValidation from '../_components/InputValidation'
import { Cell, GridX } from '../_components/UI'

class ClasseurInfos extends Component {

    static contextTypes = { 
        t: func
    }

    defaultProps = {
        nom: '',
        validation: '',
        type: {
            nom: ''
        }
    }

    state = {
        edit: false
    }

    styles = {
        progressbar: {
            width: '75%'
        }
    }

    validationRules = {
        nom: 'required',
        validation: 'required'
    }

    handleChangeLimitDate = (date) => this.props.handleChangeClasseur('validation', date)

    saveClasseurInfos = () => {
        const fields = {
            nom: this.props.nom,
            validation: Moment(this.props.validation).format('YYYY-MM-DD HH:mm')
        }
        const validation = new Validator(fields, this.validationRules)
        if(validation.passes() && this.props.id) {
            this.props.putClasseur(fields)
            this.setState({edit: false})
        }
    }

    render() {
        const { nom, validation, creation, type, handleChangeClasseur } = this.props
        const { edit } = this.state
        const { t } = this.context
        const { i18nextLng } = window.localStorage
        return (
            <GridX className="grid-margin-x grid-margin-y">
                <Cell className="medium-12">
                    <GridX>
                        <Cell className="medium-10 name-details-classeur">
                            {edit ? <InputValidation    id="nom"
                                                        type="text"
                                                        labelText={t('common.label.name')}
                                                        value={nom}
                                                        onChange={handleChangeClasseur}
                                                        validationRule={this.validationRules.nom}
                                                        placeholder={t('common.classeurs.classeur_name')}/> : <p>{nom}</p>}
                        </Cell>
                        <Cell className="medium-2">
                            <a onClick={() => this.setState({edit: !this.state.edit})}>{edit ? t('common.button.cancel') : t('common.button.modify')}</a>
                        </Cell>
                    </GridX>
                </Cell>
                <Cell>
                    <GridX>
                        <Cell className="medium-6">
                            <p className="bold-info-details-classeur">{t('admin.type.complet_name')}</p> <span>{type.nom}</span>
                        </Cell>
                        <Cell className="medium-6">
                            <p className="bold-info-details-classeur">{t('classeur.deposited')}</p> <span>{Moment(creation).format('L')}</span>
                        </Cell>
                    </GridX>
                </Cell>
                <Cell className="medium-12">
                    <p className="text-alert">{t('classeur.deadline')}</p>
                            {edit ? <InputValidation    id="validation"
                                                        type="date"
                                                        value={Moment(validation)}
                                                        readOnly={true}
                                                        locale={i18nextLng}
                                                        validationRule={this.validationRules.validation}
                                                        onChange={this.handleChangeLimitDate}/> :  <span className="text-bold">{Moment(validation).format('L')}</span>}
                        {!edit &&   <div className="alert progress progress-bar-details-classeur">
                                        <div className="progress-meter" style={this.styles.progressbar}></div>
                                    </div>}
                </Cell>
                {edit &&
                        <Button id="submit-classeur-infos"
                                className="cell medium-12"
                                classNameButton="float-right"
                                onClick={this.saveClasseurInfos}
                                labelText={t('common.button.edit_save')}/>}
            </GridX>
        )
    }
}

export default translate(['sesile'])(ClasseurInfos)