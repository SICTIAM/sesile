import React, { Component } from 'react'
import { func, object } from 'prop-types'
import { Link } from 'react-router-dom'
import CircuitListClasseur from '../circuit/CircuitListClasseur'
import ClasseursButtonList from './ClasseursButtonList'
import ClasseurProgress from './ClasseurProgress'
import ClasseurStatus from './ClasseurStatus'

class ClasseursRow extends Component {

    render(){

        const {classeur, validClasseur, signClasseur, revertClasseur, refuseClasseur, removeClasseur, deleteClasseur} = this.props

        let validUsers
        const validEtape = classeur.etape_classeurs.find(etape_classeur => etape_classeur.etape_validante)
        validEtape
            ? validUsers = validEtape.users.map(user => user._prenom + " " + user._nom )
                            .concat(validEtape.user_packs.map(user_pack => user_pack.nom))
                            .join(' / ')
            : validUsers = classeur.user._prenom + " " + classeur.user._nom

        return (
            <div id={classeur.id} className="grid-x grid-padding-x classeur align-middle">

                <Link className="classeur-name cell medium-2 text-bold" to={`/classeur/${classeur.id}`}>
                    {classeur.nom}
                </Link>
                <Link className="cell medium-2 text-bold text-center" to={`/classeur/${classeur.id}`}>
                    { validUsers }
                </Link>
                <Link className="cell medium-2 text-bold text-center" to={`/classeur/${classeur.id}`}>
                    <ClasseurStatus status={classeur.status} />
                </Link>
                <Link className="cell medium-1 text-bold text-center" to={`/classeur/${classeur.id}`}>
                    { classeur.type.nom }
                </Link>
                <div className="cell medium-2 dropdown-valign" data-toggle={"example-dropdown-" + classeur.id}>
                    <ClasseurProgress creation={classeur.creation} validation={classeur.validation} />
                </div>
                <CircuitListClasseur classeurId={classeur.id} etape_classeurs={classeur.etape_classeurs} user={classeur.user} />

                <div className="cell medium-2">
                    <ClasseursButtonList classeurs={[classeur]}
                                         validClasseur={validClasseur}
                                         signClasseur={signClasseur}
                                         revertClasseur={revertClasseur}
                                         refuseClasseur={refuseClasseur}
                                         removeClasseur={removeClasseur}
                                         deleteClasseur={deleteClasseur}
                    />
                </div>
                <div className="cell medium-1 text-center">
                    <input type="checkbox" id={classeur.id} checked={classeur.checked || false} onChange={this.props.checkClasseur} className="checkClasseur" />
                </div>
            </div>

        )
    }
}

ClasseursRow.PropTypes = {
    classeur: object.isRequired,
    checkClasseur: func.isRequired,
    validClasseur: func,
    signClasseur: func,
    refuseClasseur: func,
    removeClasseur: func,
    deleteClasseur: func
}

export default ClasseursRow