import React, {Component} from 'react'
import {array, func, object} from 'prop-types'
import {translate} from 'react-i18next'

import ClasseurInfos from '../classeur/ClasseurInfos'
import {handleErrors} from '../_utils/Utils'
import {basicNotification} from '../_components/Notifications'
import DocumentsView from '../classeur/DocumentsView'
import ClasseurActions from '../classeur/ClasseurActions'
import ClasseursButtonList from '../classeur/ClasseursButtonList'
import CircuitClasseur from '../circuit/CircuitClasseur'
import {refusClasseur, actionClasseur} from '../_utils/Classeur'
import {Cell, GridX} from '../_components/UI'
import ClasseurStatus from '../classeur/ClasseurStatus'

import History from '../_utils/History'
import {Button, Form, Textarea} from "../_components/Form";
import InputValidation from "../_components/InputValidation";
import Moment from "moment";
import ClasseurVisibilitySelect from "../classeur/ClasseurVisibilitySelect";
import SearchUserAndGroup from "../_components/SearchUserAndGroup";

class AdminClasseur extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func,
        user: object
    }

    state = {
        classeur: {
            id: null,
            nom: '',
            validation: '',
            user: {_prenom: '', _nom: ''},
            type: {nom: ''},
            etape_classeurs: [],
            copy: [],
            description: '',
            actions: [],
            documents: []
        },
        user: {},
        action: '',
        editClasseur: false,
        signatureInProgress: false
    }

    componentDidMount() {
        if (this.state.classeur.id !== this.props.match.params.classeurId) {
            this.getClasseur(this.props.match.params.classeurId)
        }
    }

    getClasseur(id) {
        fetch(Routing.generate('sesile_classeur_classeurapi_getclasseurbyidasuser', {
            orgId: this.props.match.params.collectiviteId,
            classeurId: id
        }), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                this.setState({classeur: json})
                json.documents.map(document => {
                    let htmlIdDocument = `#document-dropdown-${document.id}`
                    $(htmlIdDocument).foundation()
                })
            })
    }

    reOrderSteps() {
        this.setState((prevState) => {
            prevState.classeur.etape_classeurs.map((etape_classeur, key) => (etape_classeur.ordre = key))
        })
    }

    currentCircleClassName = (etape_classeur) => {
        if (etape_classeur.etape_valide) {
            return "success text-success"
        } else if (etape_classeur.etape_validante) {
            return "warning text-warning"
        } else {
            return "gray text-gray"
        }
    }
    currentTextClassName = (etape_classeur) => {
        if (etape_classeur.etape_valide) {
            return "text-success"
        } else if (etape_classeur.etape_validante) {
            return "text-warning"
        } else {
            return "text-gray"
        }
    }
    isLastStep = (etape_classeur) => {
        return this.props.editable && !etape_classeur.etape_valide && !etape_classeur.etape_validante
    }
    revertClasseurs = (e, classeurs) => {
        classeurs.map(classeur => {
            actionClasseur(this, 'sesile_classeur_classeurapi_retractclasseur', classeur.id)
        })
    }
    removeClasseurs = (e, classeurs) => {
        classeurs.map(classeur => {
            actionClasseur(this, 'sesile_classeur_classeurapi_removeclasseur', classeur.id)
        })
    }
    deleteClasseurs = (e, classeurs) => {
        classeurs.map(classeur => {
            actionClasseur(this, 'sesile_classeur_classeurapi_deleteclasseur', classeur.id, 'DELETE')
        })
    }

    render() {
        const {classeur, user, editClasseur} = this.state
        const {t} = this.context
        const listUsers = classeur.copy.map(user => <li className="medium-12"
                                                        key={user.id}>{user._prenom + " " + user._nom}</li>)
        const visibilitiesStatus = ["Privé", "Public", "Privé a partir de moi", "Circuit de validation"]
        const stepsCircuit = classeur.etape_classeurs.map((etape_classeur, key) =>
            <StepCircuit
                key={key}
                stepKey={key}
                etape_classeur={etape_classeur}
                currentCircleClassName={this.currentCircleClassName}
                isLastStep={this.isLastStep}
                currentTextClassName={this.currentTextClassName}
                collectiviteId={this.props.match.params.collectiviteId}/>)
        return (
            <GridX className="details-classeur">
                <Cell>
                    <GridX className="align-middle align-right">
                        <Cell className="large-12 medium-12 small-12 text-center text-uppercase text-bold">
                            <h2>{t('classeur.name')}</h2>
                        </Cell>
                    </GridX>
                    <div className="grid-x panel grid-padding-y hide-for-large">
                        {
                            classeur.id &&
                            <div className="cell large-12">
                                <div className="grid-x button-list align-middle text-center">
                                    <div className="cell medium-auto">
                                        <div className="cell medium-auto">
                                            <ButtonRevert classeurs={[classeur]} revert={this.revertClasseurs}/>
                                        </div>
                                        <div className="cell medium-auto">
                                            <ButtonRemove
                                                classeurs={[classeur]} remove={this.removeClasseurs}/>
                                        </div>
                                        <div className="cell medium-auto">
                                            <ButtonDelete
                                                classeurs={[classeur]}
                                                deleteClasseur={this.deleteClasseurs}/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        }
                    </div>
                    <div className="grid-x grid-margin-x">
                        <div className="cell large-8 medium-12 small-12">
                            {classeur.documents.length >= 1 &&
                            <DocumentsView
                                user={this.context.user}
                                documents={Object.assign([], classeur.documents)}
                                classeurId={classeur.id}
                                classeurType={Object.assign({}, classeur.type)}
                                status={classeur.status}
                                editClasseur={false}/>}
                        </div>
                        <div className="cell large-4 medium-12 small-12">
                            <div className="grid-x panel grid-padding-y show-for-large">
                                {
                                    classeur.id &&
                                    <div className="cell large-12">
                                        <ClasseurStatus status={classeur.status}/>
                                        <div className="grid-x button-list align-middle text-center">
                                            <div className="cell medium-auto">
                                                <ButtonRevert classeurs={[classeur]} revert={this.revertClasseurs}/>
                                            </div>
                                            <div className="cell medium-auto">
                                                <ButtonRemove
                                                    classeurs={[classeur]} remove={this.removeClasseurs}/>
                                            </div>
                                            <div className="cell medium-auto">
                                                <ButtonDelete
                                                    classeurs={[classeur]}
                                                    deleteClasseur={this.deleteClasseurs}/>
                                            </div>
                                        </div>
                                    </div>
                                }
                            </div>
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
                                            label={t('common.label.name')}
                                            value={classeur.nom}/>
                                        <ClasseurField
                                            label={t('common.classeurs.date_limit')}
                                            value={Moment(classeur.validation).format('LL')}/>
                                        <ClasseurField
                                            label={t('common.classeurs.label.visibility')}
                                            value={visibilitiesStatus[classeur.visibilite]}/>
                                        <ClasseurField
                                            label={t('common.label.description')}
                                            value={classeur.description || t('common.description_not_specified')}/>
                                        <GridX className="grid-margin-x grid-padding-x">
                                            <Cell>
                                                <label htmlFor="classeur-info-type"
                                                       className="text-capitalize text-bold">
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
                                                    {classeur.type.nom}
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
                                                    {Moment(classeur.creation).format('LL')}
                                                </span>
                                            </Cell>
                                        </GridX>
                                        {classeur.copy.length > 0 &&
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
                                    </Form>
                                </div>
                            </div>
                            {(classeur.id && classeur.user && this.props.match.params.collectiviteId) &&
                            <div className="grid-x panel grid-padding-y">
                                <div className="cell small-12 medium-12 large-12">
                                    <div className="grid-x grid-margin-x grid-padding-x">
                                        <h3 className="cell small-12 medium-12 large-12">
                                            {t('admin.circuit.complet_name')}
                                        </h3>
                                    </div>
                                    <div className="grid-x grid-margin-x grid-padding-x circuit-list">
                                        <div className="cell small-12 medium-12 large-12">
                                            <div
                                                className={
                                                    `align-middle
                                                                    ${this.props.etapeDeposante ?
                                                        ("text-warning") :
                                                        ("text-success")}`}
                                                style={{
                                                    marginBottom: '10px',
                                                    width: '100%',
                                                    minHeight: '5em',
                                                    display: 'flex',
                                                    boxShadow: 'rgba(34, 36, 38, 0.15) 0px 1px 2px 0px',
                                                    borderRadius: '0.285714rem',
                                                    border: '1px solid',
                                                    padding: '0.5em'
                                                }}>
                                                <div
                                                    className="text-center"
                                                    style={{display: 'inline-block', width: '2.5rem'}}>
                                                    <div
                                                        className={
                                                            this.props.etapeDeposante ?
                                                                ("circle warning text-warning") :
                                                                ("circle success text-success")}>
                                                        1
                                                    </div>
                                                </div>
                                                <div
                                                    className="text-uppercase"
                                                    style={{display: 'inline-block', width: '7rem', margin: '5px'}}>
                                                                <span
                                                                    className={
                                                                        this.props.etapeDeposante ?
                                                                            ("text-warning text-bold") :
                                                                            ("text-success text-bold")}>
                                                                    {t('admin.circuit.depositor')}
                                                                </span>
                                                </div>
                                                <div className="" style={{width: '65%'}}>
                                                                <span
                                                                    className={
                                                                        this.props.etapeDeposante ?
                                                                            ("text-warning text-bold") :
                                                                            ("text-success text-bold")}>
                                                                    {this.state.classeur.user._prenom} {this.state.classeur.user._nom}
                                                                </span>
                                                </div>
                                            </div>
                                            {stepsCircuit}
                                        </div>
                                    </div>
                                </div>
                            </div>}
                            {classeur.actions &&
                            <div className="grid-x panel grid-padding-y">
                                <div className="cell medium-12">
                                    <div className="grid-x grid-margin-x grid-padding-x">
                                        <h3 className="cell medium-12">{t('common.classeurs.comments.name')}</h3>
                                    </div>
                                    <div style={{maxHeight:"22em", overflow:"auto"}}>
                                    {classeur.actions.map((action, key) =>
                                        <div key={action.id}>
                                            <div className="align-middle" style={{display: 'flex'}}>
                                                <div className=""
                                                     style={{marginLeft: '0.5em', display: 'inline-block'}}>
                                                    <i className="fa fa-comment" style={{fontSize: '1.2em'}}/>
                                                </div>
                                                <div
                                                    className="text-left"
                                                    style={{
                                                        display: 'inline-block',
                                                        width: '90%',
                                                        marginLeft: '1em'
                                                    }}>
                                                    {action.action &&
                                                    <div>
                                                        <span className="text-bold">{action.action}</span>
                                                        <br />
                                                    </div>
                                                    }
                                                    {action.commentaire &&
                                                    <div>
                                                        {action.commentaire}
                                                        <br/>
                                                    </div>
                                                    }
                                                    <span className="text-author text-capitalize">
                                                            {action.user_action ?
                                                                `${action.user_action._prenom}  ${action.user_action._nom}` :
                                                                `${action.username}`}
                                                        </span>
                                                    <span className="text-date">
                                                            {` ${t('common.classeurs.comments.the')} ${Moment(action.date).format('Do MMMM YYYY à HH:mm:ss')}`}
                                                        </span>
                                                </div>
                                            </div>
                                            {key < classeur.actions.length - 1 &&
                                            <hr style={{height: '0.2rem', margin: '1rem auto'}}/>}
                                        </div>)}
                                    </div>
                                </div>
                            </div>}
                        </div>
                    </div>
                </Cell>
            </GridX>
        )
    }
}

export default translate(['sesile'])(AdminClasseur)

const ClasseurField = ({label, value}) => {
    return (
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
        </div>
    )
}

const StepCircuit = ({stepKey, etape_classeur, currentCircleClassName, isLastStep, currentTextClassName, collectiviteId}, {t}) => {
    return (
        <div
            className={`align-middle ${currentCircleClassName(etape_classeur)}`}
            style={{
                marginBottom: '10px',
                width: '100%',
                minHeight: '5em',
                display: 'flex',
                boxShadow: 'rgba(34, 36, 38, 0.15) 0px 1px 2px 0px',
                borderRadius: '0.285714rem',
                border: '1px solid',
                padding: '0.5em'
            }}>
            <div className="text-center" style={{display: 'inline-block', width: '2.5rem'}}>
                <div className={currentCircleClassName(etape_classeur) + " circle"}>
                    {stepKey + 2}
                </div>
            </div>
            <div style={{display: 'inline-block', width: '7rem', margin: '5px'}}>
                <span
                    className={`${currentTextClassName(etape_classeur)} text-uppercase text-bold`}>
                    {t('admin.circuit.validator')}
                </span>
            </div>
            <div
                className="align-right"
                style={{
                    width: `${isLastStep(etape_classeur) ? '60%' : '65%'}`,
                    marginTop: `${isLastStep(etape_classeur) ? '1.5em' : '0em'}`
                }}>
                <div className={`${currentTextClassName(etape_classeur)} text-bold`}>
                    {etape_classeur.users && etape_classeur.users.filter(user => user.id).map((user, userKey) =>
                        <div key={"user" + user.id} style={{display: 'inline-block', width: '100%'}}>
                            <div style={{display: 'inline-block', width: '89%'}}>
                                {user._prenom} {user._nom}
                            </div>
                        </div>)}
                    {etape_classeur.user_packs && etape_classeur.user_packs.map((user_pack, user_packKey) =>
                        <div key={"userpack" + user_pack.id} style={{display: 'inline-block', width: '100%'}}>
                            <div style={{display: 'inline-block', width: '89%'}}>
                                {user_pack.nom}
                            </div>
                        </div>)}
                </div>
            </div>
        </div>
    )
}

StepCircuit.contextTypes = {
    t: func
}

const ButtonRevert = ({classeurs, revert}, {t}) => {
    return (
        <div className="tooltip">
            <a
                onClick={(e) => revert(e, classeurs)}
                className="fa fa-repeat warning hollow"/>
            <span className="tooltiptext">{t('common.classeurs.button.revert_tooltip')}</span>
        </div>
    )
}
ButtonRevert.contextTypes = { t: func }
ButtonRevert.propTypes = {
    classeurs: array,
    revert: func
}

const ButtonRemove = ({classeurs, remove, enabled}, {t}) => {
    return (
        <div className="tooltip">
            <a
                onClick={(e) => remove(e, classeurs)}
                className="fa fa-times alert hollow"/>
            <span className="tooltiptext">{t('common.classeurs.button.remove_tooltip')}</span>
        </div>
    )
}
ButtonRemove.contextTypes = { t: func }
ButtonRemove.propTypes = {
    classeurs: array,
    remove: func
}

const ButtonDelete = ({classeurs, deleteClasseur}, {t}) => {
    return (
        <div className="tooltip">
            <a
                onClick={(e) => deleteClasseur(e, classeurs)}
                className="fa fa-trash alert hollow"/>
            <span className="tooltiptext">{t('common.classeurs.button.delete_tooltip')}</span>
        </div>
    )
}
ButtonDelete.contextTypes = { t: func }
ButtonDelete.propTypes = {
    classeurs: array,
    deleteClasseur: func
}