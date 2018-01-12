import React, { Component } from 'react'
import PropTypes, { func } from 'prop-types'
import { translate } from 'react-i18next'
import ClasseurInfos from './ClasseurInfos'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import { GridX, Cell } from '../_components/UI'
import DocumentsClasseur from './DocumentsClasseur'
import ClasseurActions from './ClasseurActions'
import ClasseursButtonList from './ClasseursButtonList'
import CircuitClasseur from '../circuit/CircuitClasseur'
import History from '../_utils/History'

class Classeur extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    state = {
        classeur: {
            id: null,
            nom: '',
            validation: '',
            user: {_prenom: '',_nom: ''},
            type: {nom: ''},
            etape_classeurs: [],
            copy: [],
            actions: []
        },
        user: {},
        newAction: {
            action: ''
        }
    }

    componentWillReceiveProps(nextProps) {
        if (this.props.classeurId !== nextProps.classeurId) {
            this.getClasseur(nextProps.classeurId)
        }
    }

    componentDidMount() {
        this.getClasseur(this.props.classeurId)

        fetch(Routing.generate('sesile_user_userapi_getcurrent'), { credentials: 'same-origin' })
            .then(response => response.json())
            .then(user => this.setState({user}))
    }

    getClasseur(id) {
        fetch(Routing.generate('sesile_classeur_classeurapi_getbyid', {id}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({classeur: json}))
    }

    putClasseur = (fields) => {

        const { classeur } = this.state

        if (this.state.newAction.action) {
            const actions = classeur.actions

            Object.assign(actions, actions.map(action => { return {
                username: action.username,
                action: action.action,
                user_action: action.user_action.id
            }}))
            actions.push(this.state.newAction)
            fields.actions = actions
        }

        const etape_classeurs = classeur.etape_classeurs
        Object.assign(etape_classeurs, classeur.etape_classeurs.map(etape_classeur => { return {
            ordre: etape_classeur.ordre,
            users: etape_classeur.users.map(user => user.id),
            user_packs: etape_classeur.user_packs.map(user_pack => user_pack.id),
        }}))

        fields.etapeClasseurs = etape_classeurs
        this.putClasseurSubmit(fields)
    }

    putClasseurSubmit (fields) {
        fetch(Routing.generate('sesile_classeur_classeurapi_update', {id: this.state.classeur.id}), {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields),
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(json => this.setState({classeur: json}))
        .then(this.context._addNotification(basicNotification(
                'success',
                this.context.t('classeur.success.edit'))))
        .catch(error => this.context._addNotification(basicNotification(
                        'error',
                        this.context.t('classeur.error.edit', {errorCode: error.status}),
                        error.statusText)))
    }

    addComment = (name,value) => {
        const {user} = this.state
        let newAction = {
            action: value,
            username: user._nom + " " + this.state.user._prenom,
            user_action: user.id
        }
        this.setState(preState => preState.newAction = newAction)

    }

    reOrderSteps() {
        this.setState((prevState) => {
            prevState.classeur.etape_classeurs.map((etape_classeur, key) => (etape_classeur.ordre = key))
        })
    }

    handleAddEtape = (stepKey) => {
        const etape_classeur = {
            ordre: null,
            user_packs: [],
            users: []
        }
        if (stepKey === null) {
            this.setState((prevState) => {prevState.classeur.etape_classeurs.push(etape_classeur)})
        }
        else {
            this.setState((prevState) => {prevState.classeur.etape_classeurs.splice(stepKey,0,etape_classeur)})
        }
        this.reOrderSteps()
    }

    handleRemoveEtape = (stepKey) => {
        this.setState(prevState => prevState.classeur.etape_classeurs.splice(stepKey,1))
        this.reOrderSteps()
    }
    handleClickDeleteUser = (stepKey, userId) => this.setState(prevState => {prevState.classeur.etape_classeurs[stepKey].users.splice(userId, 1)})
    handleClickDeleteGroup = (stepKey, groupId) => this.setState(prevState => {prevState.classeur.etape_classeurs[stepKey].user_packs.splice(groupId, 1)})
    addGroup = (stepKey, group) => this.setState(prevState => prevState.classeur.etape_classeurs[stepKey].user_packs.push(group))
    addUser = (stepKey, user) => this.setState(prevState => prevState.classeur.etape_classeurs[stepKey].users.push(user))
    handleChangeClasseur = (key, value) => this.setState(prevState => {classeur: prevState.classeur[key] = value })

    validClasseurs = (classeurs) => { classeurs.map(classeur => { this.actionClasseur('sesile_classeur_classeurapi_validclasseur', classeur.id) })}
    signClasseurs = (classeurs) => {
        let ids
        ids = []
        classeurs.map(classeur => {
            ids.push(classeur.id)
        })
        window.open(Routing.generate('jnlpSignerFiles', {id: encodeURIComponent(ids)}))
    }
    revertClasseurs = (classeurs) => { classeurs.map(classeur => { this.actionClasseur('sesile_classeur_classeurapi_retractclasseur', classeur.id) })}
    removeClasseurs = (classeurs) => { classeurs.map(classeur => { this.actionClasseur('sesile_classeur_classeurapi_removeclasseur', classeur.id) })}
    deleteClasseurs = (classeurs) => { classeurs.map(classeur => { this.actionClasseur('sesile_classeur_classeurapi_deleteclasseur', classeur.id, 'DELETE') })}
    actionClasseur (url, id, method = 'PUT') {
        fetch(Routing.generate(url, {id}),
            {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(handleErrors)
            .then(response => response.json())
            .then(classeur => this.setState({classeur}))
            .then(() => {
                if (method === 'PUT') {
                    this.context._addNotification(basicNotification(
                        'success',
                        this.context.t('classeur.success.edit')))
                } else {
                    this.context._addNotification(basicNotification(
                        'success',
                        this.context.t('classeur.success.delete')))
                    History.push(`/classeurs/supprimes`)
                }
            })
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('classeur.error.edit', {errorCode: error.status}),
                error.statusText)))
    }

    render() {
        const { t } = this.context
        const { classeur, user } = this.state

        return (
            <div className="grid-y grid-frame">
                <div className="cell medium-12 grid-y">

                    <div className="grid-x">
                        <div className="cell medium-4"></div>
                        <div className="cell medium-4">
                            <ClasseursButtonList classeur={classeur}
                                                 validClasseur={this.validClasseurs}
                                                 signClasseur={this.signClasseurs}
                                                 revertClasseur={this.revertClasseurs}
                                                 removeClasseur={this.removeClasseurs}
                                                 deleteClasseur={this.deleteClasseurs}
                            />
                        </div>
                    </div>

                    <div className="grid-x medium-12 grid-margin-x grid-padding-x">
                        <div className="cell medium-8 details-classeur">
                            {
                                classeur.documents &&
                                <DocumentsClasseur documents={classeur.documents} classeurId={classeur.id} />
                            }
                        </div>
                        <div className="cell medium-4 cell-block-y details-classeur">
                            <div className="grid-x grid-padding-y">
                                <ClasseurInfos  id={classeur.id}
                                                nom={classeur.nom}
                                                validation={classeur.validation}
                                                type={classeur.type}
                                                creation={classeur.creation}
                                                status={classeur.status}
                                                description={classeur.description}
                                                handleChangeClasseur={this.handleChangeClasseur}
                                                putClasseur={this.putClasseur} />
                            </div>

                            <div className="grid-x grid-padding-y">
                                <div className="medium-12">
                                    <div className="grid-x">
                                        <h3 className="cell medium-12">{t('admin.circuit.complet_name')}</h3>
                                    </div>
                                    {
                                        (classeur.id && classeur.user && user.collectivite) &&
                                            <CircuitClasseur classeurId={classeur.id}
                                                             etape_classeurs={classeur.etape_classeurs}
                                                             user={classeur.user}
                                                             editable={true}
                                                             addEtape={this.handleAddEtape}
                                                             addUser={this.addUser}
                                                             addGroup={this.addGroup}
                                                             removeEtape={this.handleRemoveEtape}
                                                             removeUser={this.handleClickDeleteUser}
                                                             removeGroup={this.handleClickDeleteGroup}
                                                             collectiviteId={user.collectivite.id}
                                            />
                                    }

                                    <div className="grid-x">
                                        { classeur.copy.length > 0 &&
                                        <Cell className="medium-12">
                                            <UserInCopy users={classeur.copy} />
                                        </Cell>
                                        }
                                    </div>
                                </div>
                            </div>

                            <div className="grid-x grid-padding-y">
                                <ClasseurActions actions={classeur.actions} classeur={classeur.id} addComment={this.addComment} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(Classeur)

Classeur.propTypes = {
    classeurId: PropTypes.string.isRequired
}

const UserInCopy = ({users}, {t}) => {
    const listUsers = users.map(user => <Cell className="medium-12" key={user.id}>{ user._prenom + " " + user._nom }</Cell>)
    return (
        <div className="grid-x grid-margin-x">
            <Cell className="medium-12 name-details-classeur">
                {t('classeur.users_in_copy')}
            </Cell>
            <Cell className="medium-12">
                <GridX>
                    {listUsers}
                </GridX>
            </Cell>
        </div>
    )
}

UserInCopy.contextTypes = {
    t: func
}