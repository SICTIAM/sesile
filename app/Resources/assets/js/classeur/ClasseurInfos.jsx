import React, { Component } from 'react'
import {func, number, array} from 'prop-types'
import { translate } from 'react-i18next'
import Moment from 'moment'
import Validator from 'validatorjs'

import { Button, Form, Textarea } from '../_components/Form'
import InputValidation from '../_components/InputValidation'
import { Cell, GridX } from '../_components/UI'

import ClasseurVisibilitySelect from './ClasseurVisibilitySelect'

class ClasseurInfos extends Component {

    static contextTypes = { 
        t: func
    }

    static proptypes = {
        usersCopy: array.isRequired
    }

    defaultProps = {
        nom: '',
        validation: '',
        type: {
            nom: ''
        },
        description: '',
        edit: false,
        usersCopy: []
    }

    validationRules = {
        nom: 'required',
        validation: 'required'
    }

    handleChangeLimitDate = (date) => this.props.handleChangeClasseur('validation', date)

    saveClasseurInfos = () => {
        const fields = {
            nom: this.props.nom,
            validation: Moment(this.props.validation).format('YYYY-MM-DD HH:mm'),
            description: this.props.description,
            visibilite: this.props.visibilite
        }
        const validation = new Validator(fields, this.validationRules)
        if(validation.passes() && this.props.id) {
            this.props.putClasseur(fields)
        }
    }

    render() {
        const { nom, validation, creation, type, description, status, handleChangeClasseur, usersCopy } = this.props
        const { t } = this.context
        const { i18nextLng } = window.localStorage
        //@todo verify usersCopy isn't empty
        const listUsers = usersCopy.map(user => <li className="medium-12" key={user.id}>{ user._prenom + " " + user._nom }</li>)
        const visibilitiesStatus = ["Privé", "Public", "Privé a partir de moi", "Circuit de validation"]
        return (
            <div className="grid-x panel grid-padding-y">
                <div className="cell small-12">
                    <Form onSubmit={this.saveClasseurInfos}>
                        <GridX className="grid-margin-x grid-padding-x">
                            <Cell>
                                <h3 className="text-capitalize">
                                    {t('common.infos')}
                                </h3>
                            </Cell>
                        </GridX>
                        <ClasseurField
                            edit={this.props.edit}
                            label={t('common.label.name')}
                            value={nom}>
                            <InputValidation
                                id="nom"
                                type="text"
                                labelText={`${t('common.label.name')} *`}
                                value={nom}
                                onChange={handleChangeClasseur}
                                validationRule={this.validationRules.nom}
                                placeholder={t('common.classeurs.classeur_name')}/>
                        </ClasseurField>
                        <ClasseurField
                            edit={this.props.edit}
                            label={t('common.classeurs.date_limit')}
                            value={Moment(validation).format('LL')}>
                            <InputValidation
                                id="validation"
                                type="date"
                                labelText={`${t('common.classeurs.date_limit')} *`}
                                value={Moment(validation)}
                                readOnly={true}
                                disabled={!!this.props.isFinalizedClasseur()}
                                locale={i18nextLng}
                                validationRule={this.validationRules.validation}
                                onChange={this.handleChangeLimitDate}
                                minDate={Moment()}/>
                        </ClasseurField>
                        <ClasseurField
                            edit={this.props.edit}
                            label={t('common.classeurs.label.visibility')}
                            value={visibilitiesStatus[this.props.visibilite]}>
                            <ClasseurVisibilitySelect
                                className=""
                                visibilite={this.props.visibilite}
                                disabled={!!this.props.isFinalizedClasseur()}
                                label={`${t('common.classeurs.label.visibility')} *`}
                                handleChangeClasseur={this.props.handleChangeClasseur}/>
                        </ClasseurField>
                        <ClasseurField
                            edit={this.props.edit}
                            label={t('common.label.description')}
                            value={description || t('common.description_not_specified')}>
                            <Textarea
                                id="classeur-description"
                                labelText={t('common.label.description')}
                                name="description"
                                value={description || ''}
                                disabled={!!this.props.isFinalizedClasseur()}
                                onChange={handleChangeClasseur}/>
                        </ClasseurField>
                        <GridX className="grid-margin-x grid-padding-x">
                            <Cell>
                                <label htmlFor="classeur-info-type" className="text-capitalize text-bold">
                                    {t('admin.type.name')}
                                </label>
                            </Cell>
                        </GridX>
                        <GridX className="grid-margin-x grid-padding-x">
                            <Cell>
                                <span
                                    id="classeur-info-type"
                                    style={{marginLeft: '10px'}}
                                    className="bold-info-details-classeur">
                                    {type.nom}
                                </span>
                            </Cell>
                        </GridX>
                        <GridX className="grid-margin-x grid-padding-x">
                            <Cell>
                                <label htmlFor="classeur-info-creation" className="text-bold">
                                    {t('common.classeurs.sort_label.create_date')}
                                </label>
                            </Cell>
                        </GridX>
                        <GridX className="grid-margin-x grid-padding-x">
                            <Cell>
                                <span
                                    id="classeur-info-creation"
                                    style={{marginLeft: '10px'}}
                                    className="bold-info-details-classeur">
                                    {Moment(creation).format('LL')}
                                </span>
                            </Cell>
                        </GridX>
                        {usersCopy.length > 0 &&
                            <div>
                                <GridX className="grid-margin-x grid-padding-x align-middle">
                                    <Cell className="small-12 medium-12">
                                        <label htmlFor="classeur-info-users-in-copy" className="text-bold">
                                            {t('classeur.users_in_copy')}
                                        </label>
                                    </Cell>
                                </GridX>
                                <GridX className="grid-margin-x grid-padding-x align-middle">
                                    <Cell className="small-12 medium-12">
                                        <ul
                                            id="classeur-info-users-in-copy"
                                            style={{marginLeft: '10px'}}
                                            className="no-bullet bold-info-details-classeur">
                                            {listUsers}
                                        </ul>
                                    </Cell>
                                </GridX>
                            </div>}
                        {this.props.edit &&
                            <div className="grid-x grid-margin-x grid-padding-x align-right">
                                <Button id="submit-classeur-infos"
                                        disabled={!!this.props.isFinalizedClasseur()}
                                        className="cell small-6 medium-8"
                                        classNameButton="float-right"
                                        onClick={this.saveClasseurInfos}
                                        labelText={t('common.button.edit_save')}/>
                            </div>}
                    </Form>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(ClasseurInfos)

const ClasseurField = ({edit , children, label, value}, {t}) => {
    return (
        <div>
            {edit ?
                <GridX className="grid-margin-x grid-padding-x">
                    <Cell>
                        {children}
                    </Cell>
                </GridX> :
                <div>
                    <GridX className="grid-margin-x grid-padding-x">
                        <Cell>
                            <label htmlFor={`classeur-info-${label}`} className="text-capitalize text-bold">
                                {label}
                            </label>
                        </Cell>
                    </GridX>
                    <GridX className="grid-margin-x grid-padding-x">
                        <Cell>
                            <span
                                id={`classeur-info-${label}`}
                                style={{marginLeft: '10px'}}
                                className="bold-info-details-classeur text-capitalize-first-letter">
                                {value}
                            </span>
                        </Cell>
                    </GridX>
                </div>}
        </div>
    )
}

ClasseurField.contextTypes = {
    t: func
}
