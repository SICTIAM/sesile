import React, { Component } from 'react'
import PropTypes, { func } from 'prop-types'
import { translate } from 'react-i18next'
import ClasseurInfos from './ClasseurInfos'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import DocumentsView from './DocumentsView'
import ClasseurActions from './ClasseurActions'
import ClasseursButtonList from './ClasseursButtonList'
import CircuitClasseur from '../circuit/CircuitClasseur'
import History from '../_utils/History'
import ClasseurStatus from './ClasseurStatus'
import { Cell, GridX } from "../_components/UI"

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
            description: '',
            actions: []
        },
        user: {},
        action: '',
        editClasseur: false
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

    postAction = () => {
        const { classeur } = this.state
        if (this.state.action) {
            fetch(Routing.generate('sesile_classeur_actionapi_post', {id: classeur.id}), {
                method: 'post',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: this.state.action
                }),
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(action => {
                this.setState(prevState => {
                    prevState.classeur.actions.unshift(action)
                    prevState.action = ''
                })
            })
        }
    }

    putClasseur = (fields) => {
        const { classeur } = this.state
        const etape_classeurs = classeur.etape_classeurs
        this.setState({editClasseur: false})
        Object.assign(etape_classeurs, classeur.etape_classeurs.map(etape_classeur => { return {
            ordre: etape_classeur.ordre,
            users: etape_classeur.users.map(user => user.id),
            user_packs: etape_classeur.user_packs.map(user_pack => user_pack.id),
        }}))

        fields.etapeClasseurs = etape_classeurs
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
        .then(classeur => this.setState({classeur}))
        .then(this.context._addNotification(basicNotification(
                'success',
                this.context.t('classeur.success.edit'))))
        .catch(error => this.context._addNotification(basicNotification(
                        'error',
                        this.context.t('classeur.error.edit', {errorCode: error.status}),
                        error.statusText)))
    }

    addComment = (name,value) => this.setState(prevState => prevState.action = value)

    reOrderSteps() {
        this.setState((prevState) => {
            prevState.classeur.etape_classeurs.map((etape_classeur, key) => (etape_classeur.ordre = key))
        })
    }

    handleAddEtape = (stepKey) => {
        const etape_classeur = {
            ordre: null,
            user_packs: [],
            users: [],
            autoFocus: true
        }
        if (stepKey === null) {
            this.setState((prevState) => {prevState.classeur.etape_classeurs.push(etape_classeur)})
        }
        else {
            this.setState((prevState) => {prevState.classeur.etape_classeurs.splice(stepKey,0,etape_classeur)})
        }
        this.reOrderSteps()
    }

    handleEditClasseur = (edit) => {
        if (!edit) {
            this.getClasseur(this.state.classeur.id)
        }
        this.setState(state => state.editClasseur = edit)
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
    refuseClasseurs = (classeurs) => { classeurs.map(classeur => { this.actionClasseur('sesile_classeur_classeurapi_refuseclasseur', classeur.id) })}
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
        const { classeur, user, editClasseur } = this.state
        const editable = !!(classeur.validable && editClasseur)

        return (
            <GridX className="details-classeur">
                <Cell>
                    <GridX>
                        <Cell>
                            <GridX className="align-center-middle">
                                <Cell className="medium-11 text-center">
                                    <h1>{classeur.nom}</h1>
                                </Cell>
                                <Cell className="medium-auto text-right">
                                    <ClasseurStatus status={classeur.status}/>
                                </Cell>
                            </GridX>
                        </Cell>
                    </GridX>

                    <div className="grid-x medium-12 grid-margin-x">
                        <div className="cell medium-8">
                            {
                                classeur.documents &&
                                <DocumentsView documents={Object.assign([], classeur.documents)}
                                               classeurId={classeur.id}
                                               classeurType={Object.assign({}, classeur.type)}
                                               status={classeur.status}
                                               editClasseur={classeur.validable}
                                />
                            }
                        </div>
                        <div className="cell medium-4">
                            <div className="grid-x panel grid-padding-y">
                                <div className="cell medium-12">
                                    <ClasseursButtonList classeurs={[classeur]}
                                                         validClasseur={this.validClasseurs}
                                                         signClasseur={this.signClasseurs}
                                                         revertClasseur={this.revertClasseurs}
                                                         refuseClasseur={this.refuseClasseurs}
                                                         removeClasseur={this.removeClasseurs}
                                                         deleteClasseur={this.deleteClasseurs}
                                                         display="edit"
                                    />
                                </div>
                            </div>

                            <div className="grid-x panel grid-padding-y">
                                <ClasseurInfos  id={classeur.id}
                                                nom={classeur.nom}
                                                validation={classeur.validation}
                                                type={classeur.type}
                                                creation={classeur.creation}
                                                status={classeur.status}
                                                description={classeur.description}
                                                handleChangeClasseur={this.handleChangeClasseur}
                                                putClasseur={this.putClasseur}
                                                editable={classeur.validable}
                                                handleEditClasseur={this.handleEditClasseur}
                                                edit={editClasseur}
                                                usersCopy={classeur.copy}
                                />
                            </div>

                            {
                                (classeur.id && classeur.user && user.collectivite) &&
                                    <CircuitClasseur classeurId={classeur.id}
                                                     etape_classeurs={classeur.etape_classeurs}
                                                     user={classeur.user}
                                                     etapeDeposante={classeur.etape_deposante}
                                                     editable={editable}
                                                     addEtape={this.handleAddEtape}
                                                     addUser={this.addUser}
                                                     addGroup={this.addGroup}
                                                     removeEtape={this.handleRemoveEtape}
                                                     removeUser={this.handleClickDeleteUser}
                                                     removeGroup={this.handleClickDeleteGroup}
                                                     collectiviteId={user.collectivite.id}
                                    />
                            }

                            { classeur.actions &&
                                <ClasseurActions actions={Object.assign([], classeur.actions)}
                                                 action={this.state.action}
                                                 classeur={classeur.id}
                                                 addComment={this.addComment}
                                                 submitComment={this.postAction}
                                />
                            }

                            <div className="grid-x panel grid-padding-y">
                                <div className="cell medium-12">
                                    <ClasseursButtonList classeurs={[classeur]}
                                                         validClasseur={this.validClasseurs}
                                                         signClasseur={this.signClasseurs}
                                                         revertClasseur={this.revertClasseurs}
                                                         refuseClasseur={this.refuseClasseurs}
                                                         removeClasseur={this.removeClasseurs}
                                                         deleteClasseur={this.deleteClasseurs}
                                                         display="edit"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </Cell>
            </GridX>
        )
    }
}

export default translate(['sesile'])(Classeur)