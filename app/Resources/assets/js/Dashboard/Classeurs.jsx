import React, {Component} from 'react'
import { array, string } from 'prop-types'
import { Link } from 'react-router-dom'
import ClasseursButtonList from '../classeur/ClasseursButtonList'
import ClasseurProgress from '../classeur/ClasseurProgress'

class Classeurs extends Component {

    constructor(props) {
        super(props)
    }

    render () {

        const { classeurs, title } = this.props

        return (
            <div className="grid-x grid-padding-x panel list-dashboard">
                <div className="cell medium-12 panel-heading">{ title }</div>

                {
                    classeurs &&
                        classeurs.map((classeur) => (
                            <div className="cell medium-12 panel-body" key={classeur.id}>
                                <div className="grid-x align-middle">
                                    <div className="cell medium-4 text-bold">
                                        <Link to={`/classeur/${classeur.id}`}>{ classeur.nom }</Link>
                                    </div>
                                    <div className="cell medium-4 text-justify">
                                        <ClasseurProgress creation={classeur.creation} validation={classeur.validation} />
                                    </div>
                                    <ClasseursButtonList classeur={classeur} />
                                </div>
                            </div>
                        ))
                }

            </div>
        )
    }
}

Classeurs.propTypes = {
    classeurs: array.isRequired,
    title: string
}

export default Classeurs