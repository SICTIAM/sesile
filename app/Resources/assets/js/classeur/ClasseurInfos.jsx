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
        editable: false
    }

    state = {
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
            this.setState({edit: false})
        }
    }

    render() {
        const { nom, validation, creation, type, description, status, handleChangeClasseur, editable } = this.props
        const { edit } = this.state
        const { t } = this.context
        const { i18nextLng } = window.localStorage
        return (
            <div className="cell medium-12">

                <Form onSubmit={this.saveClasseurInfos}>
                    <GridX>
                        <Cell className="medium-8 name-details-classeur">
                            { edit ? <InputValidation    id="nom"
                                                        type="text"
                                                        labelText={t('common.label.name')}
                                                        value={nom}
                                                        onChange={handleChangeClasseur}
                                                        validationRule={this.validationRules.nom}
                                                        placeholder={t('common.classeurs.classeur_name')}/>
                                : <h3>{nom}</h3>
                            }
                        </Cell>
                        <Cell className="medium-4 text-right">
                            {
                                editable &&
                                <a onClick={() => this.setState({edit: !this.state.edit})} className="button">
                                    {
                                        edit
                                            ? "Annuler"
                                            : "Modifier"
                                    }
                                </a>
                            }
                        </Cell>
                    </GridX>

                    <GridX className="grid-margin-x grid-padding-x">
                        <Cell className="medium-6">
                            <span className="info-details-classeur">{t('admin.type.name')} </span>
                            <span className="bold-info-details-classeur">{type.nom}</span>
                        </Cell>
                        <Cell className="medium-6">
                            <span className="info-details-classeur">{t('common.classeurs.status.name')} </span>
                            <StatusClasseur status={status} className="bold-info-details-classeur" />
                        </Cell>
                    </GridX>

                    <GridX className="grid-margin-x grid-padding-x">
                        <Cell className="medium-6">
                            <span className="info-details-classeur">{t('classeur.deposited')} </span>
                            <span className="bold-info-details-classeur">{Moment(creation).format('L')}</span>
                        </Cell>

                        <Cell className="medium-6">

                            <ClasseurProgress creation={creation} validation={validation} />

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

                    {(description || edit) &&
                    <GridX className="grid-margin-x grid-padding-x">
                        <Cell>
                            <span className="bold-info-details-classeur">{t('common.label.description')}</span>
                            {edit ? <Textarea id="classeur-description"
                                              name="description"
                                              value={description}
                                              onChange={handleChangeClasseur}/> : <p>{description}</p>
                            }
                        </Cell>
                    </GridX>
                    }

                    {edit &&
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <Button id="submit-classeur-infos"
                                className="cell medium-12"
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

const StatusClasseur = ({status, className}, {t}) => {
    let statusName
    switch (status) {
        case 0:
            statusName = "refuse"
            break
        case 1:
            statusName = "pending"
            break
        case 2:
            statusName = "finish"
            break
        case 3:
            statusName = "remote"
            break
        case 4:
            statusName = "retract"
            break
    }

    return (
        <span className={className}>{statusName && t('common.classeurs.status.' + statusName)}</span>
    )
}

StatusClasseur.contextTypes = {
    t: func
}