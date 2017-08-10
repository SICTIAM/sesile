import React, {Component} from 'react'
import PropTypes from 'prop-types'

class CircuitListClasseur extends Component {
    render () {

        const classeur = this.props.classeur

        return (
            <div className="dropdown-pane dropdown-pane-circuit" id={"example-dropdown-" + classeur.id} data-dropdown data-hover="true" data-hover-pane="true">
                <div className="grid-x grid-margin-y">
                    <div className="cell medium-12">
                        <h3>circuit de validation</h3>
                    </div>
                </div>

                <div className="grid-x grid-margin-y">
                    <div className="cell auto text-center"><div className="circle success">1</div></div>
                    { classeur.etape_classeurs.map((etape_classeur, key) =>
                        <div className="cell auto text-center" key={etape_classeur.id}><div className="circle success">{key + 2}</div></div>
                    )}
                </div>

                <div className="grid-x align-center-middle grid-padding-x grid-margin-y">
                    <div className="cell medium-2 text-center"><div className="circle success">1</div></div>
                    <div className="cell medium-4"><span className="text-success">Déposant</span></div>
                    <div className="cell medium-6"><span className="text-success text-bold">{classeur.user._prenom} {classeur.user._nom}</span></div>
                </div>
                { classeur.etape_classeurs.map((etape_classeur, key) =>
                    <div className="grid-x align-center-middle grid-padding-x grid-margin-y">
                        <div className="cell medium-2 text-center"><div className="circle success">{key + 2}</div></div>
                        <div className="cell medium-4"><span className="text-success">Validant</span></div>
                        <div className="cell medium-6">
                            <span className="text-success text-bold">
                            {etape_classeur.users.map(user =>
                                <div key={user.id}>{user.id} - {user._prenom} {user._nom}</div>
                            )}
                                {etape_classeur.user_packs.map(user_pack =>
                                    <div key={user_pack.id}>{user_pack.nom}</div>
                                )}
                            </span>
                        </div>
                    </div>
                )}
            </div>
        )
    }
}

CircuitListClasseur.PropTypes = {
    classeur: PropTypes.object.isRequired,
}
{/*<div className="dropdown-pane dropdown-pane-circuit" id={"example-dropdown-" + classeur.id} data-position="bottom" data-alignment="center" data-dropdown data-auto-focus="true">*/}
export default CircuitListClasseur