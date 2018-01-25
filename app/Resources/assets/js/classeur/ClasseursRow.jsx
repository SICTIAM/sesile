import React, { Component } from 'react'
import Moment from 'moment';
import { func, object } from 'prop-types'
import { Link } from 'react-router-dom'
import { Avatar } from '../_components/Form'
import CircuitListClasseur from '../circuit/CircuitListClasseur'
import ClasseursButtonList from './ClasseursButtonList'
import ClasseurProgress from './ClasseurProgress'

class ClasseursRow extends Component {

    render(){

        const {classeur, validClasseur, signClasseur, revertClasseur, removeClasseur, deleteClasseur} = this.props

        let validEtape,validUsers
        validEtape = classeur.etape_classeurs.find(etape_classeur => etape_classeur.etape_validante)
        validUsers = validEtape.users.map(user => user._prenom + " " + user._nom )
                            .concat(validEtape.user_packs.map(user_pack => user_pack.nom))
                            .join(' / ')

        return (
            <div id={classeur.id} className="grid-x grid-padding-x grid-padding-y classeur align-middle">

                <Link className="classeur-name cell medium-3 text-bold" to={`/classeur/${classeur.id}`}>
                    {classeur.nom}
                </Link>
                <Link className="cell medium-2 text-bold" to={`/classeur/${classeur.id}`}>
                    { validUsers }
                </Link>
                <Link className="cell medium-2 text-bold" to={`/classeur/${classeur.id}`}>{Moment(classeur.creation).format('L')}</Link>
                <div className="cell medium-2" data-toggle={"example-dropdown-" + classeur.id}>
                    <ClasseurProgress creation={classeur.creation} validation={classeur.validation} />
                </div>
                <CircuitListClasseur classeurId={classeur.id} etape_classeurs={classeur.etape_classeurs} user={classeur.user} />

                <div className="cell medium-2">
                    <ClasseursButtonList classeur={classeur}
                                         validClasseur={validClasseur}
                                         signClasseur={signClasseur}
                                         revertClasseur={revertClasseur}
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
    removeClasseur: func,
    deleteClasseur: func
}

export default ClasseursRow