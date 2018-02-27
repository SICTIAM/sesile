import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import Moment from 'moment'
import Validator from 'validatorjs'
import { Button, Form, Textarea } from '../_components/Form'
import InputValidation from '../_components/InputValidation'
import { Cell, GridX } from '../_components/UI'
import ClasseurProgress from './ClasseurProgress'

class ClasseurInfos extends Component {

    static contextTypes = { 
        t: func
    }

    defaultProps = {
        nom: '',
        validation: '',
        type: {
            nom: ''
        },
        description: '',
        editable: false,
        edit: false
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
            description: this.props.description
        }
        const validation = new Validator(fields, this.validationRules)
        if(validation.passes() && this.props.id) {
            this.props.putClasseur(fields)
        }
    }

    render() {
        const { nom, validation, creation, type, description, status, handleChangeClasseur, editable, edit, handleEditClasseur, usersCopy } = this.props
        const { t } = this.context
        const { i18nextLng } = window.localStorage
        const listUsers = usersCopy.map(user => <li className="medium-12" key={user.id}>{ user._prenom + " " + user._nom }</li>)

        return (
            <div className="cell medium-12">

                <Form onSubmit={this.saveClasseurInfos}>
                    <GridX className="grid-margin-x grid-padding-x">
                        <Cell className="medium-8 name-details-classeur">
                            { edit ? <InputValidation   id="nom"
                                                        type="text"
                                                        value={nom}
                                                        onChange={handleChangeClasseur}
                                                        validationRule={this.validationRules.nom}
                                                        placeholder={t('common.classeurs.classeur_name')}/>
                                : <h3>{nom}</h3>
                            }
                        </Cell>
                        <Cell className="medium-4 text-right">
                            {
                                editable && !edit &&
                                <a onClick={() => handleEditClasseur(!edit)} className="button hollow">
                                    { t('common.button.modify') }
                                </a>
                            }
                        </Cell>
                    </GridX>

                    <GridX className="grid-margin-x grid-padding-x">
                        <Cell className="medium-6">
                            <span className="info-details-classeur">{t('admin.type.name')}</span>
                        </Cell>
                        <Cell className="medium-6">
                            <span className="bold-info-details-classeur">{type.nom}</span>
                        </Cell>
                    </GridX>
                    <GridX className="grid-margin-x grid-padding-x">
                        <Cell className="medium-6">
                            <span className="info-details-classeur">{t('classeur.deposited')}</span>
                        </Cell>
                        <Cell className="medium-6">
                            <span className="bold-info-details-classeur">{Moment(creation).format('L')}</span>
                        </Cell>
                    </GridX>

                    <GridX>
                        <Cell className="medium-12">
                            <ClasseurProgress creation={creation} validation={validation} status={status} edit={true} />
                        </Cell>
                    </GridX>

                    <GridX className="grid-margin-x grid-padding-x">
                        <Cell className="medium-12">
                            {edit && <InputValidation   id="validation"
                                                        type="date"
                                                        value={Moment(validation)}
                                                        readOnly={true}
                                                        locale={i18nextLng}
                                                        validationRule={this.validationRules.validation}
                                                        onChange={this.handleChangeLimitDate}/>
                            }
                        </Cell>
                    </GridX>

                    <GridX className="grid-margin-x grid-padding-x align-middle">
                        <Cell className="medium-6">
                            <span className="info-details-classeur">{t('classeur.users_in_copy')}</span>
                        </Cell>
                        <Cell className="medium-6">
                            <span className="bold-info-details-classeur">{listUsers}</span>
                        </Cell>
                    </GridX>

                    {(description || edit) &&
                    <GridX className="grid-margin-x grid-padding-x">
                        <Cell>
                            <span className="info-details-classeur">{t('common.label.description')}</span>
                        </Cell>
                    </GridX>
                    }

                    {(description || edit) &&
                    <GridX className="grid-margin-x grid-padding-x">
                        <Cell>
                            {edit ? <Textarea id="classeur-description"
                                              name="description"
                                              value={description || ''}
                                              onChange={handleChangeClasseur}/>
                                : <p className="bold-info-details-classeur">{description}</p>
                            }
                        </Cell>
                    </GridX>
                    }

                    {edit &&
                    <div className="grid-x grid-margin-x grid-padding-x align-middle">
                        <div className="cell medium-4">
                            { <a onClick={() => handleEditClasseur(!edit)} className="float-left">{ t('common.button.cancel') }</a> }
                        </div>
                        <Button id="submit-classeur-infos"
                                className="cell medium-8"
                                classNameButton="float-right"
                                onClick={this.saveClasseurInfos}
                                labelText={t('common.button.edit_save')}/>
                    </div>
                        }
                </Form>
            </div>
        )
    }
}

export default translate(['sesile'])(ClasseurInfos)