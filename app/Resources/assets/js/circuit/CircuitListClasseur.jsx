import React, {Component} from 'react'
import PropTypes from 'prop-types'

class CircuitListClasseur extends Component {
    constructor(props) {
        super(props);
        this.state = {etapesClasseur: null};
    }

    componentDidMount() {
        fetch(Routing.generate('list_etapeclasseur_api') + "/" + this.props.classeurId, { credentials: 'same-origin' })
            .then(response => response.json())
            .then(json => {
                this.setState({etapesClasseur: json})
            });
    }

    render () {

        const etapesClasseur = this.state.etapesClasseur

        return (
            <div className="dropdown-pane dropdown-pane-circuit" id={"example-dropdown-" + this.props.classeurId} data-dropdown data-hover="true" data-hover-pane="true">
                <div className="grid-x grid-margin-y">
                    <div className="cell medium-12">
                        <h3>circuit de validation</h3>
                    </div>
                </div>

                <div className="grid-x grid-margin-y">
                    <div className="cell auto text-center"><div className="circle success">1</div></div>
                    { etapesClasseur ? (etapesClasseur.map((etape_classeur, key) =>
                        <div className="cell auto text-center" key={"circuit-" + key}><div className={
                            etape_classeur.etape_valide ?
                                ("circle success")
                                : ("circle gray")
                        }>{key + 2}</div></div>
                    )) : (<div>Chargement...</div>)
                    }
                </div>

                <div className="grid-x align-center-middle grid-padding-x grid-margin-y">
                    <div className="cell medium-2 text-center"><div className="circle success">1</div></div>
                    <div className="cell medium-4"><span className="text-success">Déposant</span></div>
                    <div className="cell medium-6"><span className="text-success text-bold">{this.props.user._prenom} {this.props.user._nom}</span></div>
                </div>
                { etapesClasseur ? (etapesClasseur.map((etape_classeur, key) =>
                    <div key={"etape_classeur-" + key} className="grid-x align-center-middle grid-padding-x grid-margin-y">
                        <div className="cell medium-2 text-center"><div className={
                            etape_classeur.etape_valide ?
                                ("circle success")
                                : ("circle gray")
                        }>{key + 2}</div></div>
                        <div className="cell medium-4"><span className={
                            etape_classeur.etape_valide ?
                                ("text-success text-bold")
                                : ("text-gray text-bold")
                        }>Validant</span></div>
                        <div className="cell medium-6">
                            <span className={
                                etape_classeur.etape_valide ?
                                    ("text-success text-bold")
                                    : ("text-gray text-bold")
                            }>
                            {etape_classeur.users.map((user, key) =>
                                <div key={"user" + user.id}>{user.id} - {user._prenom} {user._nom}</div>
                            )}
                                {etape_classeur.user_packs.map(user_pack =>
                                    <div key={"userpack" + user_pack.id}>{user_pack.nom}</div>
                                )}
                            </span>
                        </div>
                    </div>
                )) : (<div>Chargement...</div>)
                }
            </div>
        )
    }
}

CircuitListClasseur.PropTypes = {
    classeurId: PropTypes.number.isRequired,
    user: PropTypes.object.isRequired,
}
{/*<div className="dropdown-pane dropdown-pane-circuit" id={"example-dropdown-" + classeur.id} data-position="bottom" data-alignment="center" data-dropdown data-auto-focus="true">*/}
export default CircuitListClasseur