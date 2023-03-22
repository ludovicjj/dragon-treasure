import React from 'react';

const Field = React.forwardRef(function (props, ref) {
    const {name, children} = props

    return <div className="mb-3">
        <label htmlFor={name} className="form-label">{children}</label>
        <input type="text" name={name} id={name} ref={ref} className="form-control"/>
    </div>
})

export default class Home extends React.Component {
    constructor(props) {
        super(props)
        this.handleSubmit = this.handleSubmit.bind(this);
        this.username = React.createRef()
        this.password = React.createRef()
    }

    handleSubmit(e) {
        e.preventDefault();
        const username = this.username.current
        const password = this.password.current

        console.log(
            username.value,
            password.value
        )
    }

    render () {
        return <form onSubmit={this.handleSubmit}>
            <Field name="username" ref={this.username}>Username</Field>
            <Field name="password" ref={this.password}>Password</Field>
            <button className="btn btn-primary">Submit</button>
        </form>;
    }
}