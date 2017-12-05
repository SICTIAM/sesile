import React, { Component } from 'react'
import PropTypes, { func } from 'prop-types'
import { translate } from 'react-i18next'
import ClasseurInfos from './ClasseurInfos'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import DocumentsClasseur from "./DocumentsClasseur";
import { GridX, Cell } from '../_components/UI';

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
            copy: []
        }
    }

    componentWillReceiveProps(nextProps) {
        if (this.props.classeurId !== nextProps.classeurId) {
            this.getClasseur(nextProps.classeurId)
        }
    }

    componentDidMount() {
        this.getClasseur(this.props.classeurId)
    }

    getClasseur(id) {
        fetch(Routing.generate('sesile_classeur_classeurapi_getbyid', {id}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({classeur: json}))
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
        .then(json => {
            this.context._addNotification(basicNotification(
                'success',
                this.context.t('classeur.success.edit')))
            this.setState({classeur: json})
        })
        .catch(error => this.context._addNotification(basicNotification(
            'error',
            this.context.t('classeur.error.edit', {errorCode: error.status}),
            error.statusText)))
    }

    handleChangeClasseur = (key, value) => this.setState(prevState => {classeur: prevState.classeur[key] = value })

    render() {
        const { t } = this.context
        const { classeur } = this.state
        const listEtapeClasseur = this.state.classeur.etape_classeurs.map((etape_classeur, key) =>
        <div className="cell auto text-center" key={key}>
            <div className="circle success">
                {key + 2}
            </div>
        </div>)

        const listEtapeClasseurUser = classeur.etape_classeurs.map((etape_classeur, key) =>
            <EtapeClasseurUser etapeClasseur={etape_classeur} id={key} key={key}/>)
        return (
            <div className="grid-y grid-frame details-classeur">
                <div className="cell medium-12 grid-y">
                    <div className="grid-x medium-12">
                        <div className="cell medium-8 doc-details-classeur">
                            {
                                classeur.documents &&
                                <DocumentsClasseur documents={classeur.documents} classeurId={classeur.id} />
                            }
                        </div>
                        <div className="cell medium-4 infos-details-classeur">
                            <div className="grid-x grid-margin-y">
                                <Cell className="medium-12">
                                    <ClasseurInfos  id={classeur.id}
                                                    nom={classeur.nom}
                                                    validation={classeur.validation}
                                                    type={classeur.type}
                                                    creation={classeur.creation}
                                                    handleChangeClasseur={this.handleChangeClasseur}
                                                    putClasseur={this.putClasseur} />
                                </Cell>
                                <Cell className="medium-12 name-details-classeur">
                                    <p>{t('admin.circuit.complet_name')}</p>
                                </Cell>
                                <Cell className="medium-12 circuit-validation-details-classeur">
                                    <div className="grid-x grid-margin-y">
                                        <div className="cell auto text-center"><div className="circle success">1</div></div>
                                        {listEtapeClasseur}
                                    </div>
                                    <div className="grid-x align-center-middle grid-padding-x grid-margin-y">
                                        <div className="cell medium-2 text-center"><div className="circle success">1</div></div>
                                        <div className="cell medium-4"><span className="text-success">Déposant</span></div>
                                        <div className="cell medium-6"><span className="text-success text-bold">{classeur.user._prenom} {classeur.user._nom}</span></div>
                                    </div>
                                    {listEtapeClasseurUser}
                                </Cell>
                                {classeur.copy.length > 0 &&
                                    <Cell className="medium-12">
                                        <UserInCopy users={classeur.copy} />
                                    </Cell>
                                }
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

const EtapeClasseurUser = ({etapeClasseur, id}) => {
    const users = etapeClasseur.users.map((user, key) =>
        <div key={key}>{user.id} - {user._prenom} {user._nom}</div>
    )

    const userPacks = etapeClasseur.user_packs.map((user_pack, key) =>
        <div key={key}>{user_pack.nom}</div>
    )

    return (
        <div className="grid-x align-center-middle grid-padding-x grid-margin-y" key={id}>
            <div className="cell medium-2 text-center"><div className="circle success">{id + 2}</div></div>
            <div className="cell medium-4"><span className="text-success">Validant</span></div>
            <div className="cell medium-6">
                <span className="text-success text-bold">
                    {users}
                    {userPacks}
                </span>
            </div>
        </div>
    )
}

EtapeClasseurUser.propTypes = {
    etapeClasseur: PropTypes.object.isRequired,
    id: PropTypes.number.isRequired
}

const UserInCopy = ({users}) => {
    const listUsers = users.map(user => <Cell className="medium-12" key={user.id}>{ user._prenom + " " + user._nom }</Cell>)
    return (
        <div className="grid-x grid-margin-x grid-margin-y">
            <Cell className="medium-12 name-details-classeur">
                Utilisateurs en copie
            </Cell>
            <Cell className="medium-12">
                <GridX>
                    {listUsers}
                </GridX>
            </Cell>
        </div>
    )
}