import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Moment from 'moment'

class Classeur extends Component {

    constructor(props) {
        super(props)
        this.state = {
            classeur: null
        }
    }

    componentDidMount() {
        this.getClasseur(this.props.classeurId)
    }

    getClasseur(classeurId) {
        fetch(Routing.generate('sesile_classeur_classeurapi_getbyid', {id: classeurId}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({classeur: json}))
    }

    render() {
        const classeur = this.state.classeur
        return (
            classeur &&
                <div className="grid-x details-classeur">
                    <div className="cell medium-12 bold-info-details-classeur">
                        Votre classeur
                    </div>
                    <div className="cell medium-8 doc-details-classeur">
                    </div>
                    <div className="cell medium-4 infos-details-classeur">
                        <InfosClasseur classeur={classeur} key={classeur.id}/>
                    </div>
                </div>
        )
    }
}

Classeur.propTypes = {
    classeurId: PropTypes.string.isRequired
}

const InfosClasseur = ({classeur}) => {
    const listEtapeClasseur = classeur.etape_classeurs.map((etape_classeur, key) =>
        <div className="cell auto text-center" key={key}>
            <div className="circle success">
                {key + 2}
            </div>
        </div>)

    const listEtapeClasseurUser = classeur.etape_classeurs.map((etape_classeur, key) =>
        <EtapeClasseurUser etapeClasseur={etape_classeur} id={key} key={key}/>)

    return (
        <div className="grid-x grid-margin-x grid-margin-y">
            <div className="cell medium-12 bold-info-details-classeur">
                {classeur.nom}
            </div>
            <div className="cell medium-6">
                Type de classeur <span className="bold-info-details-classeur">{classeur.type.nom}</span>
            </div>
            <div className="cell medium-6">
                Déposé le <span className="bold-info-details-classeur">{Moment(classeur.creation).format('L')}</span>
            </div>
            <div className="cell medium-12">
                <p className="text-alert">Date limite le <span className="text-bold">{Moment(classeur.validation).format('L')}</span></p>
                <div className="alert progress progress-bar-details-classeur">
                    <div className="progress-meter" style={styles.progressbar}></div>
                </div>
            </div>
            <div className="cell medium-12 bold-info-details-classeur">
                Circuit de validation
            </div>
            <div className="cell medium-6 circuit-validation-details-classeur">
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
            </div>
        </div>
    )
}

InfosClasseur.propTypes = {
    classeur: PropTypes.object.isRequired
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

const styles = {
    progressbar: {
        width: '75%'
    }
}

export default Classeur