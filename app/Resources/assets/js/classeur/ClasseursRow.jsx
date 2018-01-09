import React, { Component } from 'react'
import Moment from 'moment';
import PropTypes from 'prop-types'
import { Link } from 'react-router-dom'
import { Avatar } from '../_components/Form'
import CircuitListClasseur from '../circuit/CircuitListClasseur'
import ClasseursButtonList from './ClasseursButtonList'
import ClasseurProgress from './ClasseurProgress'

class ClasseursRow extends Component {

    render(){

        const {classeur, validClasseur} = this.props

        return (
            <div id={classeur.id} className="grid-x grid-padding-x grid-padding-y classeur align-middle">
                <div className="cell medium-2">
                    <div className="grid-x grid-margin-x align-center-middle">
                        <div className="cell medium-3">
                            {classeur.user.path ?
                                <Avatar size="48" nom={classeur.user._nom} src={"/uploads/avatars/" + classeur.user.path}/> :
                                <Avatar size="48" nom={classeur.user._nom}/>
                            }
                        </div>
                        <div className="cell medium-9">
                            <div className="text-bold">{classeur.user._prenom} {classeur.user._nom}</div>
                        </div>
                    </div>

                </div>
                <Link className="classeur-name cell medium-3" to={`/classeur/${classeur.id}`}>
                    <div>
                        <span className="text-bold">{classeur.nom}</span>
                    </div>
                </Link>
                <div className="cell medium-2">Déposé le <span className="text-bold">{Moment(classeur.creation).format('L')}</span></div>
                <div className="cell medium-2" data-toggle={"example-dropdown-" + classeur.id}>
                    <ClasseurProgress creation={classeur.creation} validation={classeur.validation} />
                </div>
                <CircuitListClasseur classeurId={classeur.id} etape_classeurs={classeur.etape_classeurs} user={classeur.user} />

                <div className="cell medium-2">
                    <ClasseursButtonList classeur={classeur} validClasseur={validClasseur} />
                </div>
                <div className="cell medium-1 text-center">
                    <input type="checkbox" id={classeur.id} checked={classeur.checked || false} onChange={this.props.checkClasseur} className="checkClasseur" />
                </div>
            </div>

        )
    }
}

ClasseursRow.PropTypes = {
    classeur: PropTypes.object.isRequired,
    checkClasseur: PropTypes.func.isRequired,
    validClasseur: PropTypes.func
}

export default ClasseursRow