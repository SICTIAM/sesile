import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'

import ClasseurInfos from './ClasseurInfos'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import DocumentsView from './DocumentsView'
import ClasseurActions from './ClasseurActions'
import ClasseursButtonList from './ClasseursButtonList'
import CircuitClasseur from '../circuit/CircuitClasseur'
import { refusClasseur, actionClasseur } from '../_utils/Classeur'
import { Cell, GridX } from '../_components/UI'
import ClasseurStatus from './ClasseurStatus'

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
            description: '',
            actions: []
        },
        user: {},
        action: '',
        signatureInProgress: false
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

    componentWillUnmount() {
        clearInterval(this.interval)
        this.timer && clearTimeout(this.timer)
    }

    getClasseur(id) {
        fetch(Routing.generate('sesile_classeur_classeurapi_getbyid', {orgId: this.props.user.current_org_id, classeurId: id}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                this.setState({classeur: json})
                json.documents.map(document => {
                    let htmlIdDocument = `#document-dropdown-${document.id}`
                    $(htmlIdDocument).foundation()
                })
            })
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

    isEditableClasseur = () => {
        return (this.state.classeur.signable_and_last_validant || this.state.classeur.validable) && !this.state.signatureInProgress
    }
    handleRemoveEtape = (stepKey) => {
        this.setState(prevState => prevState.classeur.etape_classeurs.splice(stepKey,1))
        this.reOrderSteps()
    }
    handleClickDeleteUser = (stepKey, userId) => {
        this.setState(prevState => {prevState.classeur.etape_classeurs[stepKey].users.splice(userId, 1)})
    }
    handleClickDeleteGroup = (stepKey, groupId) => {
        this.setState(prevState => {prevState.classeur.etape_classeurs[stepKey].user_packs.splice(groupId, 1)})
    }
    addGroup = (stepKey, group) => {
        this.setState(prevState => prevState.classeur.etape_classeurs[stepKey].user_packs.push(group))
    }
    addUser = (stepKey, user) => {
        this.setState(prevState => prevState.classeur.etape_classeurs[stepKey].users.push(user))
    }
    handleChangeClasseur = (key, value) => {
        this.setState(prevState => {classeur: prevState.classeur[key] = value })
    }
    validClasseurs = (e, classeurs) => {
        classeurs.map(classeur => {
            actionClasseur(this, 'sesile_classeur_classeurapi_validclasseur', classeur.id)
        })
    }
    signClasseurs = (e, classeurs, role = '') => {
        let ids
        ids = []
        classeurs.map(classeur => {
            ids.push(classeur.id)
        })
        window.open(Routing.generate('jnlpSignerFiles', {id: encodeURIComponent(ids), role: role}))
        this.setState({signatureInProgress: true})
        this.interval = setInterval(() => {
            this.getClasseurStatus(classeurs[0].id)
        }, 10000)
    }
    revertClasseurs = (e, classeurs) => {
        classeurs.map(classeur => {
            actionClasseur(this, 'sesile_classeur_classeurapi_retractclasseur', classeur.id)
        })
    }
    refuseClasseurs = (e, classeurs, motif) => {
        classeurs.map(classeur => {
            refusClasseur(this, 'sesile_classeur_classeurapi_refuseclasseur', classeur.id, motif)
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
    isFinalizedClasseur = () => this.state.classeur.status === 2
    getClasseurStatus = (id) => {
        fetch(
            Routing.generate(
                'sesile_classeur_classeurapi_statusclasseur',
                {orgId: this.props.user.current_org_id, id}),
                {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                const classeurStatus = json[0]
                if(classeurStatus.id === this.state.classeur.id && classeurStatus.status !== this.state.classeur.status) {
                    clearInterval(this.interval)
                    this.getClasseur(this.state.classeur.id)
                    this.timer = setTimeout(() => History.push('/classeurs/valides'), 5000)
                }
            })
    }
    render() {
        const { classeur, user } = this.state
        return (
            <GridX className="details-classeur">
                <Cell>
                    <GridX className="align-middle align-right">
                        <Cell className="large-12 medium-12 small-12 text-center text-uppercase text-bold">
                            <h2>{this.context.t('classeur.name')}</h2>
                        </Cell>
                    </GridX>

                    <div className="grid-x panel grid-padding-y hide-for-large">
                        {
                            classeur.id &&
                            <div className="cell large-12">
                                <ClasseursButtonList classeurs={[classeur]}
                                                     validClasseur={this.validClasseurs}
                                                     signClasseur={this.signClasseurs}
                                                     revertClasseur={this.revertClasseurs}
                                                     refuseClasseur={this.refuseClasseurs}
                                                     removeClasseur={this.removeClasseurs}
                                                     deleteClasseur={this.deleteClasseurs}
                                                     signatureInProgress={this.state.signatureInProgress}
                                                     display="edit"
                                                     id={"button-list-" + classeur.id}
                                                     user={user}
                                />
                            </div>
                        }
                    </div>

                    <div className="grid-x grid-margin-x">
                        <div className="cell large-8 medium-12 small-12">
                            {
                                classeur.documents &&
                                <DocumentsView
                                    user={this.props.user}
                                    documents={Object.assign([], classeur.documents)}
                                    classeurId={classeur.id}
                                    classeurType={Object.assign({}, classeur.type)}
                                    status={classeur.status}
                                    editClasseur={this.isEditableClasseur()}/>
                            }
                        </div>
                        <div className="cell large-4 medium-12 small-12">
                            <div className="grid-x panel grid-padding-y show-for-large">
                                {
                                    classeur.id &&
                                    <div className="cell large-12">
                                        {!this.state.signatureInProgress ?
                                            <ClasseurStatus status={classeur.status}/> :
                                            <ClasseurStatus status={5}/>}
                                        <ClasseursButtonList classeurs={[classeur]}
                                                             validClasseur={this.validClasseurs}
                                                             signClasseur={this.signClasseurs}
                                                             revertClasseur={this.revertClasseurs}
                                                             refuseClasseur={this.refuseClasseurs}
                                                             removeClasseur={this.removeClasseurs}
                                                             deleteClasseur={this.deleteClasseurs}
                                                             signatureInProgress={this.state.signatureInProgress}
                                                             display="edit"
                                                             id={"button-list-top-" + classeur.id}
                                                             user={user}
                                        />
                                    </div>
                                }
                            </div>
                            <ClasseurInfos  id={classeur.id}
                                            visibilite={classeur.visibilite}
                                            nom={classeur.nom}
                                            validation={classeur.validation}
                                            type={classeur.type}
                                            creation={classeur.creation}
                                            status={classeur.status}
                                            description={classeur.description}
                                            handleChangeClasseur={this.handleChangeClasseur}
                                            putClasseur={this.putClasseur}
                                            isFinalizedClasseur={this.isFinalizedClasseur}
                                            edit={this.isEditableClasseur()}
                                            usersCopy={classeur.copy}/>

                            {(classeur.id && classeur.user && user.current_org_id) &&
                                <CircuitClasseur classeurId={classeur.id}
                                                 etape_classeurs={classeur.etape_classeurs}
                                                 user={classeur.user}
                                                 etapeDeposante={classeur.etape_deposante}
                                                 editable={classeur.validable}
                                                 edit={this.isEditableClasseur()}
                                                 putClasseur={this.putClasseur}
                                                 addEtape={this.handleAddEtape}
                                                 addUser={this.addUser}
                                                 addGroup={this.addGroup}
                                                 removeEtape={this.handleRemoveEtape}
                                                 removeUser={this.handleClickDeleteUser}
                                                 removeGroup={this.handleClickDeleteGroup}
                                                 isFinalizedClasseur={this.isFinalizedClasseur}
                                                 collectiviteId={user.current_org_id}/>}
                            {classeur.actions &&
                                <ClasseurActions actions={Object.assign([], classeur.actions)}
                                                 action={this.state.action}
                                                 classeur={classeur.id}
                                                 addComment={this.addComment}
                                                 submitComment={this.postAction}
                                />
                            }
                        </div>
                    </div>
                </Cell>
            </GridX>
        )
    }
}

export default translate(['sesile'])(Classeur)