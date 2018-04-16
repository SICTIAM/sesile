import React, { Component } from 'react'
import { func, object, int } from 'prop-types'
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
            <div id={classeur.id} className="grid-x grid-padding-x panel-body align-middle">

                <Link className="classeur-name cell small-6 medium-5 large-2 text-bold" to={`/classeur/${classeur.id}`}>
                    {classeur.nom}
                </Link>
                <Link className="cell large-2 text-bold text-center show-for-large" to={`/classeur/${classeur.id}`}>
                    { classeur.status !== 2 &&
                        validUsers
                    }
                </Link>
                <Link className="cell large-2 text-bold text-center show-for-large" to={`/classeur/${classeur.id}`}>
                    <ClasseurStatus status={classeur.status} />
                </Link>
                <Link className="cell large-1 text-bold text-center show-for-large" to={`/classeur/${classeur.id}`}>
                    { classeur.type.nom }
                </Link>
                <div className="cell small-6 medium-5 large-2 dropdown-valign" data-toggle={"example-dropdown-" + classeur.id}>
                    <ClasseurProgress creation={classeur.creation} validation={classeur.validation} />
                </div>
                <CircuitListClasseur classeurId={classeur.id} etape_classeurs={classeur.etape_classeurs} user={classeur.user} />

                <div className="cell large-2 show-for-large">
                    <ClasseursButtonList classeurs={[classeur]}
                                         validClasseur={validClasseur}
                                         signClasseur={signClasseur}
                                         revertClasseur={revertClasseur}
                                         refuseClasseur={refuseClasseur}
                                         removeClasseur={removeClasseur}
                                         deleteClasseur={deleteClasseur}
                                         id={"button-list-" + classeur.id}
                                         user={this.props.user}
                    />
                </div>
                <div className="cell medium-2 large-1 show-for-medium text-center">
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